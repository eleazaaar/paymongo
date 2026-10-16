<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Checkout | Paymongo</title>
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #eff6ff;
            --text: #172033;
            --muted: #64748b;
            --border: #dbe3ef;
            --background: #f3f6fc;
            --success: #15803d;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 40px 16px;
            font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            color: var(--text);
            background: radial-gradient(circle at top left, #e5efff 0, transparent 36%), var(--background);
        }

        button, input { font: inherit; }

        .container {
            width: 100%;
            max-width: 650px;
            margin: 0 auto;
            padding: 32px;
            background: #fff;
            border: 1px solid rgba(219, 227, 239, .8);
            border-radius: 20px;
            box-shadow: 0 18px 55px rgba(31, 54, 92, .09);
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 30px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 21px;
            font-weight: 800;
            letter-spacing: -.7px;
        }

        .brand-mark {
            display: grid;
            place-items: center;
            width: 38px;
            height: 38px;
            border-radius: 12px;
            color: white;
            background: var(--primary);
            font-size: 19px;
            font-weight: 800;
        }

        .secure {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--muted);
            font-size: 12px;
        }

        h1 {
            margin: 0;
            font-size: clamp(25px, 5vw, 32px);
            letter-spacing: -1px;
        }

        .subtitle {
            margin: 9px 0 27px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .amount-panel {
            padding: 20px;
            background: #f8faff;
            border: 1px solid #edf1f8;
            border-radius: 14px;
        }

        .field-label, .section-title {
            display: block;
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: 700;
        }

        .amount-wrap { position: relative; }

        .currency {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            font-size: 20px;
            font-weight: 700;
        }

        input[type="number"], input[type="tel"] {
            width: 100%;
            min-height: 49px;
            padding: 13px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            outline: none;
            background: #fff;
            color: var(--text);
            transition: border-color .2s, box-shadow .2s;
        }

        #amount {
            padding-left: 43px;
            font-size: 22px;
            font-weight: 700;
        }

        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
        }

        input:disabled {
            background: #f1f5f9;
            cursor: not-allowed;
        }

        .quick-amounts {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 9px;
            margin-top: 12px;
        }

        .quick-amounts button {
            padding: 10px 6px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: #fff;
            color: #334155;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: .2s;
        }

        .quick-amounts button:hover,
        .quick-amounts button.active {
            border-color: var(--primary);
            background: var(--primary-light);
            color: var(--primary);
        }

        .payment-section { margin-top: 28px; }

        .section-heading {
            margin: 0 0 13px;
            font-size: 16px;
            font-weight: 750;
        }

        .payment-options {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }

        .payment-option {
            position: relative;
            display: flex;
            flex-direction: column;
            min-height: 135px;
            padding: 16px 13px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #fff;
            cursor: pointer;
            transition: border-color .2s, background .2s, box-shadow .2s;
        }

        .payment-option:hover {
            border-color: #93b4fa;
            background: #fbfdff;
        }

        .payment-option:has(input:checked) {
            border: 2px solid var(--primary);
            padding: 15px 12px;
            background: #f5f8ff;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, .05);
        }

        .payment-option input {
            position: absolute;
            top: 13px;
            right: 13px;
            width: 17px;
            height: 17px;
            accent-color: var(--primary);
        }

        .payment-icon {
            display: grid;
            place-items: center;
            width: 42px;
            height: 36px;
            margin-bottom: 14px;
            border-radius: 9px;
            background: #f1f5f9;
            color: #334155;
            font-size: 14px;
            font-weight: 800;
        }

        .payment-icon.gcash { color: #0878ed; background: #e7f2ff; }
        .payment-icon.maya { color: #008f62; background: #e7f9f1; }
        .payment-icon.card { color: #274690; background: #e9efff; }

        .payment-name {
            display: block;
            padding-right: 12px;
            font-size: 14px;
            font-weight: 750;
        }

        .payment-description {
            display: block;
            margin-top: 5px;
            color: var(--muted);
            font-size: 11px;
            line-height: 1.5;
        }

        .details-panel {
            margin-top: 17px;
            padding: 18px;
            border: 1px solid #d8e6ff;
            border-radius: 12px;
            background: #f6f9ff;
        }

        .details-heading {
            display: flex;
            align-items: center;
            gap: 11px;
            margin-bottom: 15px;
        }

        .details-heading .payment-icon { margin: 0; flex-shrink: 0; }

        .details-heading strong {
            display: block;
            font-size: 14px;
        }

        .details-heading small {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.4;
        }

        .hint {
            margin-top: 10px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.5;
        }

        .card-notice {
            margin-top: 17px;
            padding: 16px;
            border: 1px solid #dbe5ff;
            border-radius: 12px;
            background: #f5f7ff;
            color: #334b80;
            font-size: 13px;
            line-height: 1.6;
        }

        .demo-notice {
            display: flex;
            gap: 10px;
            margin-top: 18px;
            padding: 13px 15px;
            border: 1px solid #fed7aa;
            border-radius: 10px;
            background: #fff7ed;
            color: #9a3412;
            font-size: 12px;
            line-height: 1.5;
        }

        .demo-notice strong { display: block; margin-bottom: 2px; }

        .submit-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            width: 100%;
            min-height: 52px;
            margin-top: 20px;
            padding: 14px;
            border: 0;
            border-radius: 10px;
            background: var(--primary);
            color: #fff;
            font-size: 15px;
            font-weight: 750;
            cursor: pointer;
            transition: background .2s, transform .2s;
        }

        .submit-btn:hover { background: var(--primary-dark); }
        .submit-btn:active { transform: scale(.99); }
        .submit-btn:disabled { opacity: .7; cursor: wait; }

        .footer {
            display: flex;
            justify-content: center;
            gap: 18px;
            flex-wrap: wrap;
            margin-top: 22px;
            padding-top: 17px;
            border-top: 1px solid #edf1f6;
            color: var(--muted);
            font-size: 11px;
        }

        .hidden { display: none !important; }

        @media (max-width: 540px) {
            body { padding: 16px 10px; }
            .container { padding: 21px 17px; border-radius: 15px; }
            .topbar { margin-bottom: 25px; }
            .brand { font-size: 18px; }
            .secure { font-size: 11px; }
            .payment-options { grid-template-columns: 1fr; }
            .payment-option { flex-direction: row; align-items: center; min-height: auto; gap: 12px; padding: 13px; }
            .payment-option:has(input:checked) { padding: 12px; }
            .payment-icon { margin: 0; }
            .payment-description { margin-top: 3px; }
            .payment-option input { top: 50%; transform: translateY(-50%); }
            .payment-name { padding-right: 20px; }
            .quick-amounts { gap: 6px; }
            .quick-amounts button { font-size: 12px; }
            .footer { gap: 10px; }
        }
    </style>
</head>
<body>
    <main class="container">
        <div class="topbar">
            <div class="brand"><span class="brand-mark">P</span><span>Paymongo</span></div>
            <div class="secure"><span aria-hidden="true">🔒</span> Secure Checkout</div>
        </div>

        <h1>Payment Checkout</h1>
        <p class="subtitle">Choose your preferred payment method and continue securely.</p>

        <form action="app/classes/controllers/PaymongoController.php?action=createPayment" method="POST" id="checkoutForm">
            <section class="amount-panel">
                <label class="field-label" for="amount">Payment Amount (PHP)</label>
                <div class="amount-wrap">
                    <span class="currency">₱</span>
                    <input type="number" id="amount" name="amount" min="1" max="50000" step="0.01" value="100.00" required>
                </div>

                <div class="quick-amounts">
                    <button type="button" data-amount="50">₱50</button>
                    <button type="button" data-amount="100" class="active">₱100</button>
                    <button type="button" data-amount="200">₱200</button>
                    <button type="button" data-amount="500">₱500</button>
                </div>
            </section>

            <section class="payment-section">
                <h2 class="section-heading">Select Payment Method</h2>

                <div class="payment-options">
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="QRPH" checked>
                        <span class="payment-icon gcash">QR</span>
                        <span><span class="payment-name">QR PH</span><span class="payment-description">Pay using QRPH</span></span>
                    </label>

                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="GCASH">
                        <span class="payment-icon gcash">G</span>
                        <span><span class="payment-name">GCash</span><span class="payment-description">Pay using GCash</span></span>
                    </label>

                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="PAYMAYA">
                        <span class="payment-icon maya">M</span>
                        <span><span class="payment-name">Maya</span><span class="payment-description">Pay using Maya</span></span>
                    </label>

                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="CARDS">
                        <span class="payment-icon card">▤</span>
                        <span><span class="payment-name">Credit / Debit Card</span><span class="payment-description">Paymongo's hosted checkout</span></span>
                    </label>
                </div>
            </section>

            <div class="demo-notice">
                <span aria-hidden="true">ⓘ</span>
                <span><strong>Payment Notice</strong>Confirm your payment details before continuing. Your payment will be processed by the selected payment provider.</span>
            </div>

            <button type="submit" class="submit-btn" id="submitBtn">Continue to Wallet Confirmation <span aria-hidden="true">→</span></button>
        </form>

        <footer class="footer">
            <span>🔒 Secure connection</span>
            <span>Powered by Paymongo</span>
            <span>Do not share your OTP</span>
        </footer>
    </main>

    <script>
        const form = document.getElementById('checkoutForm');
        const amountInput = document.getElementById('amount');
        const submitBtn = document.getElementById('submitBtn');
        const amountButtons = document.querySelectorAll('[data-amount]');
        const paymentInputs = document.querySelectorAll('input[name="payment_method"]');
        const paymentInfoTitle = document.getElementById('paymentInfoTitle');
        const paymentInfoText = document.getElementById('paymentInfoText');

        const paymentMessages = {
            QRPH: {
                title: 'QRPH payment',
                text: "You'll continue to PayMongo Checkout to complete your QRPH payment. Make sure your QRPH is active before continuing."
            },
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
            amountButtons.forEach(function(button) {
                button.classList.toggle('active', Number(button.dataset.amount) === Number(amountInput.value));
            });
        }

        amountButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                amountInput.value = this.dataset.amount;
                updateAmountButtons();
            });
        });

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
            const selected = document.querySelector('input[name="payment_method"]:checked');

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
            submitBtn.textContent = 'Please wait...';
        });

        updatePaymentMethod();
        updateAmountButtons();
    </script>
</body>
</html>