 
<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Checkout | PayMongo</title>

    <style>
        * {
            box-sizing: border-box;
        }

        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --text: #172033;
            --muted: #64748b;
            --border: #e2e8f0;
            --background: #f3f6fc;
            --surface: #ffffff;
        }

        body {
            margin: 0;
            min-height: 100vh;
            padding: 32px 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--text);
            background: var(--background);
        }

        button,
        input {
            font: inherit;
        }

        .checkout {
            width: 100%;
            max-width: 510px;
            padding: 32px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            box-shadow: 0 15px 45px rgba(31, 54, 92, .09);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 11px;
            margin-bottom: 30px;
            font-size: 19px;
            font-weight: 800;
        }

        .brand-mark {
            width: 40px;
            height: 40px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: var(--primary);
            color: #fff;
            font-size: 21px;
        }

        .eyebrow {
            margin: 0 0 9px;
            color: var(--primary);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1.4px;
            text-transform: uppercase;
        }

        h1 {
            margin: 0 0 10px;
            font-size: 28px;
            letter-spacing: -.7px;
        }

        .subtitle {
            margin: 0 0 28px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.7;
        }

        .field-label {
            display: block;
            margin-bottom: 10px;
            font-size: 13px;
            font-weight: 700;
        }

        .amount-wrap {
            position: relative;
            margin-bottom: 12px;
        }

        .currency {
            position: absolute;
            top: 50%;
            left: 16px;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: 20px;
            font-weight: 700;
        }

        .amount-input {
            width: 100%;
            height: 60px;
            padding: 12px 16px 12px 44px;
            border: 1px solid var(--border);
            border-radius: 11px;
            outline: none;
            color: var(--text);
            font-size: 23px;
            font-weight: 800;
            transition: border-color .2s, box-shadow .2s;
        }

        .amount-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .1);
        }

        .amount-help {
            margin: 8px 0 20px;
            color: var(--muted);
            font-size: 11px;
        }

        .quick-amounts {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 28px;
        }

        .quick-amount {
            padding: 10px 4px;
            border: 1px solid var(--border);
            border-radius: 9px;
            background: #fff;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: .2s;
        }

        .quick-amount:hover,
        .quick-amount.active {
            border-color: var(--primary);
            background: #eff6ff;
            color: var(--primary);
        }

        .section-title {
            margin: 0 0 12px;
            font-size: 14px;
            font-weight: 800;
        }

        .payment-options {
            display: grid;
            gap: 10px;
        }

        .payment-option {
            position: relative;
        }

        .payment-option input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .payment-label {
            display: flex;
            align-items: center;
            gap: 13px;
            min-height: 76px;
            padding: 14px;
            border: 1px solid var(--border);
            border-radius: 12px;
            cursor: pointer;
            transition: border-color .2s, background .2s;
        }

        .payment-option input:checked + .payment-label {
            border-color: var(--primary);
            background: #f8fbff;
            box-shadow: 0 0 0 1px var(--primary);
        }

        .payment-option input:focus-visible + .payment-label {
            outline: 3px solid rgba(37, 99, 235, .2);
            outline-offset: 2px;
        }

        .payment-icon {
            display: grid;
            place-items: center;
            flex: 0 0 44px;
            width: 44px;
            height: 44px;
            border-radius: 11px;
            background: #eff6ff;
            color: var(--primary);
            font-size: 14px;
            font-weight: 800;
        }

        .payment-copy {
            flex: 1;
            min-width: 0;
        }

        .payment-name {
            display: block;
            margin-bottom: 5px;
            font-size: 13px;
            font-weight: 800;
        }

        .payment-description {
            display: block;
            color: var(--muted);
            font-size: 11px;
            line-height: 1.5;
        }

        .radio-indicator {
            display: grid;
            place-items: center;
            flex: 0 0 18px;
            width: 18px;
            height: 18px;
            border: 1.5px solid #cbd5e1;
            border-radius: 50%;
        }

        .payment-option input:checked + .payment-label .radio-indicator {
            border: 5px solid var(--primary);
        }

        .payment-info {
            margin-top: 15px;
            padding: 15px;
            border: 1px solid #dbeafe;
            border-radius: 11px;
            background: #f8fbff;
        }

        .payment-info strong {
            display: block;
            margin-bottom: 6px;
            font-size: 12px;
        }

        .payment-info p {
            margin: 0;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.7;
        }

        .submit-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            min-height: 52px;
            margin-top: 23px;
            padding: 14px;
            border: 0;
            border-radius: 11px;
            background: var(--primary);
            color: #fff;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            transition: background .2s;
        }

        .submit-btn:hover {
            background: var(--primary-hover);
        }

        .submit-btn:disabled {
            opacity: .65;
            cursor: wait;
        }

        .security-note {
            display: flex;
            align-items: flex-start;
            justify-content: center;
            gap: 7px;
            margin-top: 17px;
            color: var(--muted);
            font-size: 11px;
            line-height: 1.6;
            text-align: center;
        }

        .footer {
            margin-top: 25px;
            padding-top: 18px;
            border-top: 1px solid #edf1f6;
            color: var(--muted);
            font-size: 11px;
            text-align: center;
        }

        .hidden {
            display: none;
        }

        @media (max-width: 480px) {
            body {
                padding: 14px 10px;
                align-items: flex-start;
            }

            .checkout {
                margin: 8px 0;
                padding: 24px 18px;
                border-radius: 15px;
            }

            h1 {
                font-size: 25px;
            }

            .payment-label {
                padding: 12px;
            }

            .quick-amount {
                font-size: 11px;
            }
        }
    </style>
</head>

<body>
    <main class="checkout">
        <div class="brand">
            <span class="brand-mark">&lt;/&gt;</span>
            <span>PayMongo Checkout</span>
        </div>

        <p class="eyebrow">Secure payment</p>
        <h1>Complete your payment</h1>
        <p class="subtitle">
            Enter your payment amount and select your preferred payment method
            to continue to PayMongo's secure checkout.
        </p>

        <form
            action="app/classes/controllers/PaymongoController.php?action=createPayment"
            method="POST"
            id="checkoutForm"
        >
            <label class="field-label" for="amount">Payment amount</label>

            <div class="amount-wrap">
                <span class="currency">₱</span>
                <input
                    class="amount-input"
                    type="number"
                    id="amount"
                    name="amount"
                    min="1"
                    max="50000"
                    step="0.01"
                    value="100.00"
                    inputmode="decimal"
                    required
                >
            </div>

            <p class="amount-help">Minimum ₱1.00 · Maximum ₱50,000.00</p>

            <div class="quick-amounts" aria-label="Quick amount selection">
                <button type="button" class="quick-amount" data-amount="50">₱50</button>
                <button type="button" class="quick-amount active" data-amount="100">₱100</button>
                <button type="button" class="quick-amount" data-amount="200">₱200</button>
                <button type="button" class="quick-amount" data-amount="500">₱500</button>
            </div>

            <h2 class="section-title">Choose payment method</h2>

            <div class="payment-options">
                <div class="payment-option">
                    <input
                        type="radio"
                        id="gcash"
                        name="payment_method"
                        value="QRPH"
                        checked
                        required
                    >
                    <label class="payment-label" for="gcash">
                        <span class="payment-icon">G</span>
                        <span class="payment-copy">
                            <span class="payment-name">GCash</span>
                            <span class="payment-description">
                                Pay using your GCash account.
                            </span>
                        </span>
                        <span class="radio-indicator"></span>
                    </label>
                </div>

                <div class="payment-option">
                    <input
                        type="radio"
                        id="paymaya"
                        name="payment_method"
                        value="PAYMAYA"
                    >
                    <label class="payment-label" for="paymaya">
                        <span class="payment-icon">M</span>
                        <span class="payment-copy">
                            <span class="payment-name">Maya</span>
                            <span class="payment-description">
                                Pay with your Maya wallet, if enabled.
                            </span>
                        </span>
                        <span class="radio-indicator"></span>
                    </label>
                </div>

                <div class="payment-option">
                    <input
                        type="radio"
                        id="cards"
                        name="payment_method"
                        value="CARDS"
                    >
                    <label class="payment-label" for="cards">
                        <span class="payment-icon">CC</span>
                        <span class="payment-copy">
                            <span class="payment-name">Credit or Debit Card</span>
                            <span class="payment-description">
                                Pay using a supported card.
                            </span>
                        </span>
                        <span class="radio-indicator"></span>
                    </label>
                </div>
            </div>

            <div class="payment-info" id="paymentInfo" aria-live="polite">
                <strong id="paymentInfoTitle">GCash payment</strong>
                <p id="paymentInfoText">
                    You'll continue to PayMongo Checkout to complete your GCash payment.
                    Make sure your GCash account is active before continuing.
                </p>
            </div>

            <button class="submit-btn" id="submitBtn" type="submit">
                Continue to PayMongo Checkout
                <span aria-hidden="true">→</span>
            </button>

            <div class="security-note">
                <span aria-hidden="true">🔒</span>
                <span>
                    Your payment details are handled securely through PayMongo's
                    hosted checkout. Do not enter your card details on this page.
                </span>
            </div>
        </form>

        <div class="footer">
            Powered by PayMongo
        </div>
    </main>

    <script>
        const form = document.getElementById('checkoutForm');
        const amountInput = document.getElementById('amount');
        const submitBtn = document.getElementById('submitBtn');
        const amountButtons = document.querySelectorAll('[data-amount]');
        const paymentInputs = document.querySelectorAll(
            'input[name="payment_method"]'
        );
        const paymentInfoTitle = document.getElementById('paymentInfoTitle');
        const paymentInfoText = document.getElementById('paymentInfoText');

        const paymentMessages = {
            GCASH: {
                title: 'GCash payment',
                text: "You'll continue to PayMongo Checkout to complete your GCash payment. Make sure your GCash account is active before continuing."
            },
            PAYMAYA: {
                title: 'Maya payment',
                text: "You'll continue to PayMongo Checkout to complete your Maya payment, if this method is enabled for your account."
            },
            CARDS: {
                title: 'Credit or debit card',
                text: "You'll securely enter your card details on PayMongo Checkout. Your card information is not collected on this page."
            },
            CARD: {
                title: 'Credit or debit card',
                text: "You'll securely enter your card details on PayMongo Checkout. Your card information is not collected on this page."
            }
        };

        function updateAmountButtons() {
            const currentAmount = Number(amountInput.value);

            amountButtons.forEach(function(button) {
                button.classList.toggle(
                    'active',
                    Number(button.dataset.amount) === currentAmount
                );
            });
        }

        function updatePaymentMethod() {
            const selected = document.querySelector(
                'input[name="payment_method"]:checked'
            );

            if (!selected) {
                return;
            }

            const message = paymentMessages[selected.value];

            if (message) {
                paymentInfoTitle.textContent = message.title;
                paymentInfoText.textContent = message.text;
            }
        }

        amountButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                amountInput.value = this.dataset.amount;
                updateAmountButtons();
            });
        });

        amountInput.addEventListener('input', updateAmountButtons);

        paymentInputs.forEach(function(input) {
            input.addEventListener('change', updatePaymentMethod);
        });

        form.addEventListener('submit', function(event) {
            const amount = Number(amountInput.value);
            const selected = document.querySelector(
                'input[name="payment_method"]:checked'
            );

            if (!Number.isFinite(amount) || amount < 1 || amount > 50000) {
                event.preventDefault();
                alert('Please enter an amount from ₱1.00 to ₱50,000.00.');
                amountInput.focus();
                return;
            }

            if (!selected) {
                event.preventDefault();
                alert('Please select a payment method.');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Connecting to PayMongo...';
        });

        updateAmountButtons();
        updatePaymentMethod();
    </script>
</body>
</html>