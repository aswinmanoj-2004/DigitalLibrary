<?php
require('../config/autoload.php');
if (!isset($_SESSION['reader_id'])) {
    header('location: readerLogin.php'); exit();
}

$dao      = new DataAccess();
$readerId = $_SESSION['reader_id'];
$bid      = isset($_GET['bid']) ? intval($_GET['bid']) : 0;
$transId  = isset($_GET['transId']) ? trim($_GET['transId']) : '';

if (!$bid || !$transId) {
    header('location: bookList.php'); exit();
}

// Verify purchase exists and belongs to reader
$purchase = $dao->query("SELECT p.*, b.bname, b.AuthorName, b.CoverImage, b.story, py.payment_method, py.payment_date 
                         FROM purchases p 
                         JOIN books b ON p.bid = b.bid 
                         LEFT JOIN payments py ON p.purchaseid = py.purchaseid
                         WHERE p.userId = '$readerId' 
                           AND p.transaction_id = '$transId' 
                           AND p.bid = '$bid' 
                           AND p.payment_status = 'completed' 
                           AND p.status = 1 
                         LIMIT 1");

if (!$purchase) {
    header('location: bookList.php'); exit();
}

$p = $purchase[0];

include("readerHeader.php");
?>

<style>
    /* Google Pay style success animation styles */
    .gpay-success-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 20px 0 60px 0;
        position: relative;
        min-height: 70vh;
    }

    .animation-container {
        position: relative;
        width: 160px;
        height: 160px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Pulsing background circle */
    .success-circle {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: #0f9d58; /* Google Pay Green */
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
        box-shadow: 0 6px 24px rgba(15, 157, 88, 0.35);
        animation: scaleUp 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }

    /* SVG checkmark styling */
    .checkmark {
        width: 48px;
        height: 48px;
        stroke: #ffffff;
        stroke-width: 5;
        stroke-linecap: round;
        stroke-linejoin: round;
        fill: none;
        stroke-dasharray: 80;
        stroke-dashoffset: 80;
        animation: drawCheckmark 0.6s ease-in-out 0.5s forwards;
    }

    /* Concentric ripples */
    .ripple {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        border-radius: 50%;
        border: 2px solid rgba(15, 157, 88, 0.25);
        width: 100px;
        height: 100px;
        z-index: 1;
        opacity: 0;
    }

    .ripple-1 {
        animation: pulseRipple 1.8s cubic-bezier(0.1, 0.8, 0.3, 1) 0.3s infinite;
    }

    .ripple-2 {
        animation: pulseRipple 1.8s cubic-bezier(0.1, 0.8, 0.3, 1) 0.9s infinite;
    }

    /* GPay Ticket Receipt Card styling */
    .receipt-card {
        width: 100%;
        max-width: 480px;
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
        padding: 32px;
        opacity: 0;
        transform: translateY(40px);
        animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.8s forwards;
        position: relative;
        overflow: visible;
        margin-top: 10px;
    }

    /* Dotted separation tear-off line */
    .tear-off {
        margin: 24px -32px;
        border-top: 2px dashed #e2e8f0;
        position: relative;
    }
    
    .tear-off::before, .tear-off::after {
        content: '';
        position: absolute;
        width: 20px;
        height: 20px;
        background: var(--bg); /* Match container background */
        border-radius: 50%;
        top: -10px;
        border: 1px solid #e2e8f0;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02);
    }
    .tear-off::before {
        left: -10px;
    }
    .tear-off::after {
        right: -10px;
    }

    /* Book Info block inside receipt */
    .book-block {
        display: grid;
        grid-template-columns: 80px 1fr;
        gap: 16px;
        align-items: center;
        background: #f8fafc;
        padding: 16px;
        border-radius: 16px;
        border: 1px solid #f1f5f9;
        margin-bottom: 20px;
    }

    .book-block img {
        width: 80px;
        height: 110px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .txn-details {
        display: grid;
        gap: 12px;
        font-size: 14px;
        color: var(--text-main);
    }

    .txn-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .txn-label {
        color: var(--text-muted);
        font-weight: 500;
    }

    .txn-val {
        font-weight: 600;
        text-align: right;
    }

    /* Keyframes */
    @keyframes scaleUp {
        0% { transform: scale(0); }
        100% { transform: scale(1); }
    }

    @keyframes drawCheckmark {
        0% { stroke-dashoffset: 80; }
        100% { stroke-dashoffset: 0; }
    }

    @keyframes pulseRipple {
        0% {
            width: 100px;
            height: 100px;
            opacity: 0.9;
        }
        100% {
            width: 280px;
            height: 280px;
            opacity: 0;
        }
    }

    @keyframes slideUp {
        0% {
            opacity: 0;
            transform: translateY(40px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div class="gpay-success-wrapper">
    <!-- Google Pay Chime Synthesizer & Ripple Animations -->
    <div class="animation-container">
        <div class="ripple ripple-1"></div>
        <div class="ripple ripple-2"></div>
        <div class="success-circle">
            <svg class="checkmark" viewBox="0 0 52 52">
                <path fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
            </svg>
        </div>
    </div>

    <!-- Success Status Headings -->
    <h2 style="font-weight: 800; font-size: 26px; color: #0f9d58; margin-bottom: 4px; text-align: center;">Payment Successful!</h2>
    <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px; text-align: center;">Your purchase is secure and instant access is granted</p>

    <!-- Receipt Card -->
    <div class="receipt-card">
        <!-- Large Google Pay style Price badge -->
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="font-size: 13px; font-weight: 700; color: #0f9d58; text-transform: uppercase; letter-spacing: 1px; display: inline-flex; align-items: center; gap: 6px; background: rgba(15, 157, 88, 0.08); padding: 6px 14px; border-radius: 20px; margin-bottom: 8px;">
                <i data-lucide="shield-check" style="width: 16px; height: 16px;"></i> Paid with Ready2Read
            </div>
            <h1 style="font-size: 38px; font-weight: 800; color: #0f9d58; margin: 0; letter-spacing: -0.5px;">
                ₹<?= number_format($p['amount'], 2) ?>
            </h1>
        </div>

        <!-- Book Block -->
        <div class="book-block">
            <img src="../uploads/<?= htmlspecialchars($p['CoverImage']) ?>" alt="Book Cover" onerror="this.src='../assets/img/placeholder.png'">
            <div>
                <h4 style="font-size: 16px; font-weight: 700; margin-bottom: 4px; line-height: 1.3; color: var(--text-main);"><?= htmlspecialchars($p['bname']) ?></h4>
                <p style="color: var(--text-muted); font-size: 13px; font-weight: 500; margin-bottom: 8px;">By <?= htmlspecialchars($p['AuthorName']) ?></p>
                <span class="badge badge-paid" style="display: inline-block;">Premium Access</span>
            </div>
        </div>

        <div class="tear-off"></div>

        <!-- Transaction Details -->
        <div class="txn-details">
            <div class="txn-row">
                <span class="txn-label">Transaction ID</span>
                <span class="txn-val" style="font-family: monospace; font-size: 13px; background: #f1f5f9; padding: 2px 8px; border-radius: 4px;"><?= htmlspecialchars($p['transaction_id']) ?></span>
            </div>
            <div class="txn-row">
                <span class="txn-label">Payment Method</span>
                <span class="txn-val"><?= htmlspecialchars($p['payment_method'] ?? 'UPI') ?></span>
            </div>
            <div class="txn-row">
                <span class="txn-label">Date & Time</span>
                <span class="txn-val"><?= date('d M Y, h:i A', strtotime($p['payment_date'] ?? $p['purchase_date'])) ?></span>
            </div>
            <div class="txn-row">
                <span class="txn-label">Status</span>
                <span class="txn-val" style="color: #0f9d58; display: flex; align-items: center; gap: 4px;">
                    <i data-lucide="check-circle" style="width: 16px; height: 16px; fill: #0f9d58; stroke: #fff;"></i> Completed
                </span>
            </div>
        </div>

        <!-- Actions -->
        <div style="margin-top: 30px; display: grid; gap: 12px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <a href="../uploadpdf/<?= htmlspecialchars($p['story']) ?>" target="_blank" class="btn" style="background: #0f9d58; box-shadow: 0 4px 12px rgba(15, 157, 88, 0.25);">
                    <i data-lucide="book-open" style="width:16px;height:16px;"></i> Read Online
                </a>
                <a href="downloadBook.php?bid=<?= $bid ?>" class="btn" style="background: var(--accent); box-shadow: 0 4px 12px rgba(124, 58, 237, 0.2);">
                    <i data-lucide="download" style="width:16px;height:16px;"></i> Download PDF
                </a>
            </div>
            <a href="myPurchases.php" class="btn-outline" style="text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <i data-lucide="shopping-bag" style="width:16px;height:16px;"></i> View My Purchases
            </a>
            <a href="readerHome.php" style="text-align: center; color: var(--text-muted); font-size: 13px; font-weight: 500; text-decoration: none; margin-top: 6px;">
                Go to Dashboard
            </a>
        </div>
    </div>
</div>

<script>
    // Audio tone synthesizer that mimics the signature Google Pay payment success sound
    window.addEventListener('DOMContentLoaded', () => {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (AudioContext) {
                const ctx = new AudioContext();

                const playChime = () => {
                    const now = ctx.currentTime;
                    
                    // Simple pleasant chord (E5 then A5) for Google Pay chime vibe
                    // Note 1: E5 (659.25Hz)
                    const osc1 = ctx.createOscillator();
                    const gain1 = ctx.createGain();
                    osc1.type = 'sine';
                    osc1.frequency.setValueAtTime(659.25, now);
                    
                    gain1.gain.setValueAtTime(0.12, now);
                    gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
                    
                    osc1.connect(gain1);
                    gain1.connect(ctx.destination);
                    
                    // Note 2: A5 (880.00Hz)
                    const osc2 = ctx.createOscillator();
                    const gain2 = ctx.createGain();
                    osc2.type = 'sine';
                    osc2.frequency.setValueAtTime(880.00, now + 0.12);
                    
                    gain2.gain.setValueAtTime(0.18, now + 0.12);
                    gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.65);
                    
                    osc2.connect(gain2);
                    gain2.connect(ctx.destination);
                    
                    osc1.start(now);
                    osc1.stop(now + 0.35);
                    
                    osc2.start(now + 0.12);
                    osc2.stop(now + 0.65);
                };

                if (ctx.state === 'suspended') {
                    // Fallback to play sound on the first click if autoplay is blocked
                    const resumeAudio = () => {
                        ctx.resume().then(() => {
                            playChime();
                            document.removeEventListener('click', resumeAudio);
                        });
                    };
                    document.addEventListener('click', resumeAudio);
                } else {
                    playChime();
                }
            }
        } catch (e) {
            console.warn("Web Audio API not supported:", e);
        }
    });
</script>

<?php include("readerFooter.php"); ?>
