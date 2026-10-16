<?php

if (!function_exists('fileExists')) {
    function fileExists($file = "") {
        for ($i = 0; $i < 10 && !file_exists($file); $i++) {
            $file = "../" . $file;
        }

        return $file;
    }
}

include_once fileExists('app/classes/models/Extras.php');

class PaymongoModel extends Extras {

    protected $db;

    public function __construct() {
        parent::__construct();
    }

    public function createOrder(
        $referenceId,
        $amount,
        $paymentMethod,
        $walletNumber = null
    ) {
        $referenceId = $this->db->real_escape_string($referenceId);
        $paymentMethod = $this->db->real_escape_string($paymentMethod);
        $amount = number_format((float) $amount, 2, ".", "");

        $walletValue = "NULL";

        if ($walletNumber !== null && $walletNumber !== "") {
            $walletNumber = $this->db->real_escape_string($walletNumber);
            $walletValue = "'$walletNumber'";
        }

        $sql = "INSERT INTO orders
                    (reference_id, amount, payment_method, wallet_number, status)
                VALUES
                    ('$referenceId', '$amount', '$paymentMethod',
                    $walletValue, 'PENDING')";

        if (!$this->db->query($sql)) {
            $this->saveSystemLog(
                "PayMongo order insert failed: " . $this->db->error
            );

            return false;
        }

        return true;
    }
    
    public function updateOrderPaymentId($referenceId, $paymentId) {
        $referenceId = $this->db->real_escape_string($referenceId);
        $paymentId = $this->db->real_escape_string($paymentId);

        $sql = "UPDATE orders SET paymongo_payment_id = '$paymentId'
                WHERE reference_id = '$referenceId'";
        if (!$this->db->query($sql)) {
            $this->saveSystemLog("Could not save PayMongo Checkout Session ID: " . $this->db->error);
            return false;
        }

        if ($this->db->affected_rows === 0) {
            $this->saveSystemLog("PayMongo Checkout Session ID was not saved; order not found or unchanged: ". $referenceId);

            // Confirm the order exists and already contains this session ID.
            $checkSql = "SELECT reference_id FROM orders
                        WHERE reference_id = '$referenceId' AND paymongo_payment_id = '$paymentId' LIMIT 1";
            $check = $this->db->query($checkSql);
            if (!$check || $check->num_rows === 0) {
                return false;
            }
        }
        return true;
    }


    public function updateOrderStatus($referenceId, $status) {
        $allowedStatuses = ["PENDING", "PAID", "FAILED"];

        if (!in_array($status, $allowedStatuses, true)) {
            return false;
        }

        $referenceId = $this->db->real_escape_string($referenceId);

        if ($status === "PAID") {
            $sql = "UPDATE orders
                    SET status = 'PAID',
                        paid_at = COALESCE(paid_at, CURRENT_TIMESTAMP)
                    WHERE reference_id = '$referenceId'
                      AND status = 'PENDING'";
        } else {
            $sql = "UPDATE orders
                    SET status = '$status'
                    WHERE reference_id = '$referenceId'
                      AND status = 'PENDING'";
        }

        if (!$this->db->query($sql)) {
            $this->saveSystemLog(
                "Could not update PayMongo order status: " . $this->db->error
            );

            return false;
        }

        if ($this->db->affected_rows === 0) {
            $this->saveSystemLog(
                "PayMongo order status unchanged or order not found: "
                . $referenceId
            );

            return false;
        }

        return true;
    }

    public function getOrderByReference($referenceId) {
        $referenceId = $this->db->real_escape_string($referenceId);

        $sql = "SELECT *
                FROM orders
                WHERE reference_id = '$referenceId'
                LIMIT 1";

        $result = $this->db->query($sql);

        if (!$result) {
            $this->saveSystemLog(
                "Could not retrieve PayMongo order: " . $this->db->error
            );

            return false;
        }

        return $result->fetch_assoc();
    }
}