<?php
require('../config/autoload.php');
if (!isset($_SESSION['reader_id'])) {
    header('location: readerLogin.php'); exit();
}

$dao      = new DataAccess();
$readerId = $_SESSION['reader_id'];
$bid      = isset($_GET['bid']) ? intval($_GET['bid']) : 0;

if (!$bid) { header('location: bookList.php'); exit(); }

$books = $dao->query("SELECT * FROM books WHERE bid='$bid' AND is_paid='paid' AND status=1");
if (!$books) { header('location: bookList.php'); exit(); }
$book = $books[0];

// Already purchased?
$purchased = $dao->query("SELECT * FROM purchases WHERE userId='$readerId' AND bid='$bid' AND payment_status='completed' AND status=1");
if ($purchased) {
    echo "<script>alert('You have already purchased this book!'); window.location='bookDetail.php?bid=$bid';</script>";
    exit();
}

$msg = "";

if (isset($_POST['btn_pay'])) {
    $paymentMethod = $_POST['payment_method'];
    $transId       = 'TXN' . time() . rand(100, 999);
    $valid         = true;

    if ($paymentMethod === 'UPI') {
        $upiUtr = isset($_POST['upi_utr']) ? trim($_POST['upi_utr']) : '';
        if (empty($upiUtr) || !preg_match('/^\d{12}$/', $upiUtr)) {
            $msg   = "Invalid UPI Transaction ID / UTR. It must be exactly 12 digits.";
            $valid = false;
        } else {
            $transId = 'UPI' . $upiUtr;
        }
    } elseif ($paymentMethod === 'Card') {
        $cardNo     = isset($_POST['card_no']) ? str_replace(' ', '', $_POST['card_no']) : '';
        $cardExpiry = isset($_POST['card_expiry']) ? trim($_POST['card_expiry']) : '';
        $cardCvv    = isset($_POST['card_cvv']) ? trim($_POST['card_cvv']) : '';

        if (empty($cardNo) || !preg_match('/^\d{16}$/', $cardNo)) {
            $msg   = "Invalid Card Number. Must be exactly 16 digits.";
            $valid = false;
        } elseif (empty($cardExpiry) || !preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $cardExpiry)) {
            $msg   = "Invalid Card Expiry date. Must be in MM/YY format.";
            $valid = false;
        } elseif (empty($cardCvv) || !preg_match('/^\d{3,4}$/', $cardCvv)) {
            $msg   = "Invalid CVV number. Must be 3 or 4 digits.";
            $valid = false;
        }
    }

    if ($valid) {
        // Insert purchase
        $purchaseData = [
            'userId'         => $readerId,
            'bid'            => $bid,
            'amount'         => $book['price'],
            'purchase_date'  => date('Y-m-d H:i:s'),
            'payment_status' => 'completed',
            'transaction_id' => $transId,
            'status'         => 1,
        ];
        $purchaseInserted = $dao->insert($purchaseData, "purchases");

        if ($purchaseInserted) {
            $purchaseRow = $dao->query("SELECT * FROM purchases WHERE transaction_id='$transId' LIMIT 1");
            $purchaseid  = $purchaseRow ? $purchaseRow[0]['purchaseid'] : 0;

            $paymentData = [
                'purchaseid'     => $purchaseid,
                'userId'         => $readerId,
                'bid'            => $bid,
                'amount'         => $book['price'],
                'payment_method' => $paymentMethod,
                'transaction_id' => $transId,
                'payment_date'   => date('Y-m-d H:i:s'),
                'status'         => 1,
            ];
            $dao->insert($paymentData, "payments");

            header("location: paymentSuccess.php?bid=$bid&transId=$transId");
            exit();
        } else {
            $msg = "Payment failed. Please try again.";
        }
    }
}
include("readerHeader.php");
?>

<style>
    /* Extra minimal styling for payment page */
    .payment-method-card {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .payment-method-card.selected {
        border-color: var(--accent);
        background: rgba(37, 99, 235, 0.05);
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.1);
    }
    .method-detail-panel {
        background: rgba(255,255,255,0.02);
        border-radius: 16px;
        padding: 20px;
        margin-top: 20px;
        border: 1px solid var(--border);
    }
    .qr-placeholder {
        background: white;
        border-radius: 16px;
        padding: 16px;
        display: inline-block;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .form-control-custom {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: var(--bg);
        font-family: inherit;
        transition: 0.2s;
    }
    .form-control-custom:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
    }
</style>

<a href="bookDetail.php?bid=<?= $bid ?>" class="btn-outline" style="display:inline-block; margin-bottom:30px;">&larr; Back to Details</a>

<?php if ($msg): ?>
<div style="padding:16px; background:rgba(239,68,68,0.1); color:#ef4444; border-radius:8px; margin-bottom:24px; border:1px solid rgba(239,68,68,0.2);"><?= $msg ?></div>
<?php endif; ?>

<div style="display:grid; grid-template-columns: 300px 1fr; gap:40px; align-items:start;">
    <!-- Order Summary -->
    <div class="glass-card" style="text-align:center;">
        <h3 style="margin-top:0; margin-bottom:20px; font-size:18px;">Order Summary</h3>
        <img src="../uploads/<?= htmlspecialchars($book['CoverImage']) ?>" style="width:100%; border-radius:8px; margin-bottom:20px; box-shadow:0 10px 30px rgba(0,0,0,0.5);" onerror="this.src='../assets/img/placeholder.png'">
        <div style="font-weight:700; font-size:18px; margin-bottom:8px;"><?= htmlspecialchars($book['bname']) ?></div>
        <div style="color:var(--text-muted); font-size:14px; margin-bottom:20px;">By <?= htmlspecialchars($book['AuthorName']) ?></div>
        <div style="font-size:28px; font-weight:800; color:#f59e0b;">₹<?= htmlspecialchars($book['price']) ?></div>
    </div>

    <!-- Checkout -->
    <div class="glass-card">
        <h3 style="margin-top:0; margin-bottom:24px; font-size:22px; display:flex; align-items:center; gap:12px;">
            Secure Checkout 🔒
        </h3>

        <form method="POST" id="paymentForm">
            <div style="margin-bottom:24px;">
                <label style="display:block; margin-bottom:12px; font-weight:600;">Select Payment Method</label>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <label class="payment-method-card" style="display:flex; align-items:center; gap:12px; padding:16px; border:1px solid var(--border); border-radius:8px; cursor:pointer; transition:0.2s;">
                        <input type="radio" name="payment_method" value="UPI" required checked style="width:18px; height:18px; accent-color:var(--accent);">
                        <span>UPI / QR Code</span>
                    </label>
                    <label class="payment-method-card" style="display:flex; align-items:center; gap:12px; padding:16px; border:1px solid var(--border); border-radius:8px; cursor:pointer; transition:0.2s;">
                        <input type="radio" name="payment_method" value="Card" required style="width:18px; height:18px; accent-color:var(--accent);">
                        <span>Credit / Debit Card</span>
                    </label>
                    <label class="payment-method-card" style="display:flex; align-items:center; gap:12px; padding:16px; border:1px solid var(--border); border-radius:8px; cursor:pointer; transition:0.2s;">
                        <input type="radio" name="payment_method" value="NetBanking" required style="width:18px; height:18px; accent-color:var(--accent);">
                        <span>Net Banking</span>
                    </label>
                    <label class="payment-method-card" style="display:flex; align-items:center; gap:12px; padding:16px; border:1px solid var(--border); border-radius:8px; cursor:pointer; transition:0.2s;">
                        <input type="radio" name="payment_method" value="Wallet" required style="width:18px; height:18px; accent-color:var(--accent);">
                        <span>Digital Wallet</span>
                    </label>
                </div>
            </div>

            <!-- Dynamic method-specific details -->
            <div id="upiDetails" class="method-detail-panel">
                <div style="text-align: center;">
                    <h4 style="margin-bottom: 12px;">Scan QR code to pay</h4>
                    <div class="qr-placeholder">
                        <?php 
                            $upiUrl = "upi://pay?pa=ready2read@okhdfcbank&pn=Ready2Read&am=" . $book['price'] . "&cu=INR";
                            $qrCodeUrl = "https://quickchart.io/qr?text=" . urlencode($upiUrl) . "&size=180&margin=2";
                        ?>
                        <img src="<?= $qrCodeUrl ?>" alt="UPI QR Code" style="width:180px; height:180px; display:block;">
                        <p style="font-size:12px; margin-top:12px;">or pay to <strong>ready2read@okhdfcbank</strong></p>
                    </div>
                    <p style="font-size:13px; color:var(--text-light); margin-top:16px;">Open any UPI app and scan this QR code</p>
                    <div style="text-align:left; margin-top:20px; max-width:320px; margin-left:auto; margin-right:auto;">
                        <label style="display:block; margin-bottom:6px; font-weight:600;">Transaction ID / UTR (12 digits) *</label>
                        <input type="text" name="upi_utr" class="form-control-custom" placeholder="e.g. 312345678901" maxlength="12" value="<?= htmlspecialchars($_POST['upi_utr'] ?? '') ?>" required>
                    </div>
                </div>
            </div>

            <div id="cardDetails" class="method-detail-panel" style="display:none;">
                <div style="display:grid; gap:16px;">
                    <div>
                        <label style="display:block; margin-bottom:6px;">Card Number *</label>
                        <input type="text" name="card_no" class="form-control-custom" placeholder="1234567890123456" maxlength="16" value="<?= htmlspecialchars($_POST['card_no'] ?? '') ?>">
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                        <div>
                            <label style="display:block; margin-bottom:6px;">Expiry (MM/YY) *</label>
                            <input type="text" name="card_expiry" class="form-control-custom" placeholder="MM/YY" value="<?= htmlspecialchars($_POST['card_expiry'] ?? '') ?>">
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:6px;">CVV *</label>
                            <input type="password" name="card_cvv" class="form-control-custom" placeholder="123" maxlength="4" value="<?= htmlspecialchars($_POST['card_cvv'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div id="netbankingDetails" class="method-detail-panel" style="display:none;">
                <div>
                    <label style="display:block; margin-bottom:6px;">Select Bank</label>
                    <select class="form-control-custom">
                        <option>State Bank of India</option>
                        <option>HDFC Bank</option>
                        <option>ICICI Bank</option>
                        <option>Axis Bank</option>
                      
                    </select>
                </div>
            </div>

            <div id="walletDetails" class="method-detail-panel" style="display:none;">
                <div>
                    <label style="display:block; margin-bottom:6px;">Select Wallet</label>
                    <select class="form-control-custom">
                        <option>Paytm</option>
                        <option>Google Pay</option>
                        <option>PhonePe</option>
                        <option>Amazon Pay</option>
                        
                    </select>
                </div>
            </div>

            <div style="padding:16px; background:rgba(59,130,246,0.1); border:1px solid rgba(59,130,246,0.2); border-radius:8px; color:#60a5fa; font-size:14px; margin:24px 0; display:flex; align-items:flex-start; gap:12px;">
                <span style="font-size:20px;">ℹ️</span>
                <span><strong>Demo Payment Portal.</strong> No real money will be charged. This will securely log a simulated transaction and grant you immediate access to the premium book.</span>
            </div>

            <button type="submit" name="btn_pay" class="btn" style="width:100%; padding:16px; font-size:18px; background:#ffa500; color:#fff; box-shadow:0 0 20px rgba(245,158,11,0.3);">
                Pay ₹<?= htmlspecialchars($book['price']) ?> Now
            </button>
            <a href="bookDetail.php?bid=<?= $bid ?>" class="btn" style="width:100%; padding:16px; font-size:18px; background:#ce2029; color:#fff; box-shadow:0 0 20px rgba(206,32,41,0.2); text-decoration:none; display:flex; align-items:center; justify-content:center; margin-top:12px;">
                Cancel Payment
            </a>
        </form>
    </div>
</div>

<script>
    // Toggle payment method details and highlight selected card
    const methodRadios = document.querySelectorAll('input[name="payment_method"]');
    const upiPanel = document.getElementById('upiDetails');
    const cardPanel = document.getElementById('cardDetails');
    const netbankingPanel = document.getElementById('netbankingDetails');
    const walletPanel = document.getElementById('walletDetails');
    const methodCards = document.querySelectorAll('.payment-method-card');

    function updatePaymentUI() {
        const selected = document.querySelector('input[name="payment_method"]:checked').value;
        // Hide all panels
        upiPanel.style.display = 'none';
        cardPanel.style.display = 'none';
        netbankingPanel.style.display = 'none';
        walletPanel.style.display = 'none';

        // Remove required attribute from all inputs
        document.getElementsByName('upi_utr')[0].removeAttribute('required');
        document.getElementsByName('card_no')[0].removeAttribute('required');
        document.getElementsByName('card_expiry')[0].removeAttribute('required');
        document.getElementsByName('card_cvv')[0].removeAttribute('required');

        // Show selected and add required
        if (selected === 'UPI') {
            upiPanel.style.display = 'block';
            document.getElementsByName('upi_utr')[0].setAttribute('required', 'required');
        } else if (selected === 'Card') {
            cardPanel.style.display = 'block';
            document.getElementsByName('card_no')[0].setAttribute('required', 'required');
            document.getElementsByName('card_expiry')[0].setAttribute('required', 'required');
            document.getElementsByName('card_cvv')[0].setAttribute('required', 'required');
        } else if (selected === 'NetBanking') {
            netbankingPanel.style.display = 'block';
        } else if (selected === 'Wallet') {
            walletPanel.style.display = 'block';
        }

        // Highlight the selected method card
        methodCards.forEach(card => {
            const radio = card.querySelector('input[type="radio"]');
            if (radio && radio.checked) {
                card.classList.add('selected');
            } else {
                card.classList.remove('selected');
            }
        });
    }

    methodRadios.forEach(radio => {
        radio.addEventListener('change', updatePaymentUI);
    });
    updatePaymentUI();
</script>

<?php include("readerFooter.php"); ?>