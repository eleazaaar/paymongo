
<?php

header('Content-Type: application/json; charset=utf-8');

if (!function_exists('fileExists')) {
    function fileExists($file="") {
        for ($i=0; $i<10 && !file_exists($file); $i++) {
            $file = "../" . $file;
        } return $file;
    }
}

include_once fileExists('app/classes/models/PaymongoModel.php');

class PaymongoController extends PaymongoModel {

    private $PAYMONGO_API_URL;
    private $PAYMONGO_SECRET_KEY;
    private $PAYMONGO_WEBHOOK_SECRET;
    private $BASE_URL;

    public function __construct() {
        parent::__construct();

        $this->PAYMONGO_API_URL = getenv('PAYMONGO_API_URL') ?: '';
        $this->PAYMONGO_SECRET_KEY = getenv('PAYMONGO_SECRET_KEY') ?: '';
        $this->PAYMONGO_WEBHOOK_SECRET = getenv('PAYMONGO_WEBHOOK_SECRET') ?: '';
        $this->BASE_URL = rtrim(getenv('BASE_URL') ?: '', '/');
    }

    private function respond($status, $message, $data = []) {
        $this->saveSystemLog("PayMongo: " . $message);

        if (!empty($data['redirect_url'])) {
            header('Location: ' . $data['redirect_url']);
            exit;
        }

        http_response_code($status ? 200 : 400);

        echo json_encode([
            'success' => $status,
            'message' => $message,
            'data' => $data
        ]);

        exit;
    }

    public function createPayment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respond(false, 'Invalid request method.');
        }

        if ($this->PAYMONGO_SECRET_KEY === '' || $this->BASE_URL === '') {
            $this->respond(false, 'PayMongo configuration is incomplete.');
        }

        $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
        $paymentMethod = strtoupper(trim($_POST['payment_method'] ?? ''));

        $paymentMethods = [
            'QRPH' => 'qrph',
            'CARDS'  => 'card',
            'CARD'   => 'card',
            'GCASH'  => 'gcash',
            'PAYMAYA' => 'paymaya',
            'MAYA'   => 'paymaya'
        ];

        if ($amount === false || $amount === null || $amount <= 0) {
            $this->respond(false, 'Invalid payment amount.');
        }

        if (!isset($paymentMethods[$paymentMethod])) {
            $this->respond(false, 'Invalid payment method.');
        }

        $paymongoMethod = $paymentMethods[$paymentMethod];

        // PayMongo Checkout Sessions use centavos.
        $amountInCentavos = (int) round($amount * 100);

        if ($amountInCentavos < 100) {
            $this->respond(false, 'The payment amount must be at least PHP 1.00.');
        }

        $referenceId = 'ORDER-' . date('YmdHis') . '-' .
            strtoupper(bin2hex(random_bytes(4)));

        $successUrl = $this->BASE_URL .
            '/success.php?reference_id=' . rawurlencode($referenceId);

        $cancelUrl = $this->BASE_URL .
            '/failure.php?reference_id=' . rawurlencode($referenceId);

        /*
         * PayMongo Hosted Checkout collects the customer's
         * wallet/card details on PayMongo's page.
         * A wallet number is not required here.
         */
        if (!$this->createOrder($referenceId, $amount, $paymentMethod)) {
            $this->respond(false, 'Could not create the order.');
        }

        $payload = [
            'data' => [
                'attributes' => [
                    'line_items' => [
                        [
                            'currency' => 'PHP',
                            'amount' => $amountInCentavos,
                            'name' => 'Order ' . $referenceId,
                            'quantity' => 1
                        ]
                    ],
                    'payment_method_types' => [$paymongoMethod],
                    'reference_number' => $referenceId,
                    'description' => 'Payment for order ' . $referenceId,
                    'success_url' => $successUrl,
                    'cancel_url' => $cancelUrl,
                    'send_email_receipt' => false,
                    'show_description' => true,
                    'show_line_items' => true
                ]
            ]
        ];

        $ch = curl_init($this->PAYMONGO_API_URL);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Basic ' .
                    base64_encode($this->PAYMONGO_SECRET_KEY . ':')
            ],
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($response === false) {
            $this->saveSystemLog(
                'PayMongo request failed: ' . $curlError
            );

            $this->respond(false, 'Could not connect to PayMongo.');
        }

        $result = json_decode($response, true);

        if ($httpCode < 200 || $httpCode >= 300) {
            $errorMessage = $result['errors'][0]['detail']
                ?? 'PayMongo rejected the checkout request.';

            $this->saveSystemLog(
                'PayMongo API error (' . $httpCode . '): ' . $response
            );

            $this->respond(false, $errorMessage);
        }

        $sessionId = $result['data']['id'] ?? '';
        $checkoutUrl = $result['data']['attributes']['checkout_url'] ?? '';

        if ($sessionId === '' || $checkoutUrl === '') {
            $this->saveSystemLog(
                'PayMongo response did not contain the session ID or checkout URL.'
            );

            $this->respond(false, 'Invalid response from PayMongo.');
        }

        if (!$this->updateOrderPaymentId($referenceId, $sessionId)) {
            $this->saveSystemLog(
                'Could not save PayMongo Checkout Session ID for ' . $referenceId
            );

            $this->respond(false, 'Could not save checkout information.');
        }

        // Redirect the customer's browser to PayMongo Hosted Checkout.
        header('Location: ' . $checkoutUrl);
        exit;
    }

    private function verifyWebhookSignature($rawBody, $signatureHeader) {
        if ($this->PAYMONGO_WEBHOOK_SECRET === '' || $signatureHeader === '') {
            return false;
        }

        $parts = [];

        foreach (explode(',', $signatureHeader) as $part) {
            $pair = explode('=', trim($part), 2);

            if (count($pair) === 2) {
                $parts[trim($pair[0])] = trim($pair[1]);
            }
        }

        $timestamp = $parts['t'] ?? '';
        $testSignature = $parts['te'] ?? '';
        $liveSignature = $parts['li'] ?? '';

        if ($timestamp === '' || !ctype_digit($timestamp)) {
            return false;
        }

        // Reject requests with timestamps more than 5 minutes old or ahead.
        if (abs(time() - (int) $timestamp) > 300) {
            return false;
        }

        $signature = hash_hmac(
            'sha256',
            $timestamp . '.' . $rawBody,
            $this->PAYMONGO_WEBHOOK_SECRET
        );

        $receivedSignature = $testSignature !== ''
            ? $testSignature
            : $liveSignature;

        return $receivedSignature !== '' &&
            hash_equals($signature, $receivedSignature);
    }

    public function webhook() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['message' => 'Method not allowed.']);
            exit;
        }

        $rawBody = file_get_contents('php://input');

        $signatureHeader = $_SERVER['HTTP_PAYMONGO_SIGNATURE']
            ?? $_SERVER['HTTP_X_PAYMONGO_SIGNATURE']
            ?? '';

        if (!$this->verifyWebhookSignature($rawBody, $signatureHeader)) {
            $this->saveSystemLog('PayMongo webhook signature verification failed.');

            http_response_code(401);
            echo json_encode(['message' => 'Invalid webhook signature.']);
            exit;
        }

        $payload = json_decode($rawBody, true);

        if (!is_array($payload)) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid JSON payload.']);
            exit;
        }

        // Current PayMongo webhook event format.
        $eventAttributes = $payload['data']['attributes'] ?? [];
        $eventType = $eventAttributes['type'] ?? '';

        // Checkout Session resource included in the event.
        $session = $eventAttributes['data'] ?? [];
        $sessionAttributes = $session['attributes'] ?? [];

        // Support the alternate event envelope shown in some examples.
        if ($eventType === '' && isset($payload['data']['type'])) {
            $eventType = $payload['data']['type'];
            $session = $payload['data']['data'] ?? [];
            $sessionAttributes = $session['attributes'] ?? [];
        }

        if ($eventType === 'checkout_session.payment.paid') {
            $referenceId = $sessionAttributes['reference_number'] ?? '';

            if ($referenceId === '') {
                $this->saveSystemLog(
                    'PayMongo paid webhook did not contain a reference number.'
                );

                http_response_code(400);
                echo json_encode(['message' => 'Missing order reference.']);
                exit;
            }

            /*
             * The model only changes PENDING orders to PAID.
             * Duplicate deliveries therefore cannot reverse a paid order.
             */
            $this->updateOrderStatus($referenceId, 'PAID');

            $this->saveSystemLog(
                'PayMongo payment confirmation received for ' . $referenceId
            );
        }

        // Acknowledge valid events, including events we do not handle.
        http_response_code(200);
        echo json_encode(['received' => true]);
        exit;
    }
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $Paymongo = new PaymongoController();
    
    if ($action == 'createPayment') {
        $Paymongo->createPayment();
        exit;
    }

    if ($action === 'webhook') {
        $Paymongo->webhook();
        exit;
    }

    http_response_code(400);
    echo json_encode(['status' => false,'reply' => 'Invalid action.']);
    exit;
}

http_response_code(400);
echo json_encode(["status" => false, "reply" => "Invalid request."]);