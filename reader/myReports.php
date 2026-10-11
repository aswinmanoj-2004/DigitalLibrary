<?php
require('../config/autoload.php');
if (!isset($_SESSION['reader_id'])) {
    header('location: readerLogin.php'); exit();
}
include("readerHeader.php");

$dao      = new DataAccess();
$readerId = $_SESSION['reader_id'];

// --- Date Filter ---
$fromDate  = isset($_GET['from']) ? $_GET['from'] : '';
$toDate    = isset($_GET['to'])   ? $_GET['to']   : '';
$hasFilter = ($fromDate !== '' && $toDate !== '');
$showAllPayments = isset($_GET['all_payments']) && $_GET['all_payments'] == '1';

$datePurchase = $hasFilter ? "AND p.purchase_date BETWEEN '$fromDate' AND '$toDate 23:59:59'" : "";
$dateDownload = $hasFilter ? "AND d.download_date BETWEEN '$fromDate' AND '$toDate 23:59:59'" : "";
$datePayment  = ($hasFilter && !$showAllPayments) ? "AND py.payment_date BETWEEN '$fromDate' AND '$toDate 23:59:59'" : "";

// === SUMMARY STATS ===
$purchaseCount = $dao->query("SELECT COUNT(*) as cnt FROM purchases p WHERE p.userId='$readerId' AND p.status=1 $datePurchase");
$totalPurchased = $purchaseCount ? $purchaseCount[0]['cnt'] : 0;

$downloadCount = $dao->query("SELECT COUNT(*) as cnt FROM downloads d WHERE d.userId='$readerId' AND d.status=1 $dateDownload");
$totalDownloaded = $downloadCount ? $downloadCount[0]['cnt'] : 0;

$spentResult = $dao->query("SELECT COALESCE(SUM(p.amount),0) as total FROM purchases p WHERE p.userId='$readerId' AND p.payment_status='completed' AND p.status=1 $datePurchase");
$totalSpent = $spentResult ? $spentResult[0]['total'] : 0;

// === BOOKS READ (Purchases) ===
$booksRead = $dao->query("
    SELECT p.purchaseid, b.bname, b.AuthorName, b.is_paid, p.purchase_date
    FROM purchases p
    LEFT JOIN books b ON p.bid = b.bid
    WHERE p.userId='$readerId' AND p.status=1 $datePurchase
    ORDER BY p.purchase_date DESC
");

// === DOWNLOADS ===
$downloads = $dao->query("
    SELECT d.downloadid, b.bname, b.AuthorName, d.download_date
    FROM downloads d
    LEFT JOIN books b ON d.bid = b.bid
    WHERE d.userId='$readerId' AND d.status=1 $dateDownload
    ORDER BY d.download_date DESC
");

// === PAYMENT HISTORY ===
$payments = $dao->query("
    SELECT py.payid, b.bname, py.amount, py.payment_method, py.transaction_id, py.payment_date
    FROM payments py
    LEFT JOIN books b ON py.bid = b.bid
    WHERE py.userId='$readerId' AND py.status=1 $datePayment
    ORDER BY py.payment_date DESC
");
?>

<style>
    .report-filter {
        background: var(--card); border: 1px solid var(--border); border-radius: 12px;
        padding: 18px 24px; margin-bottom: 28px; display: flex; align-items: center;
        gap: 14px; flex-wrap: wrap; box-shadow: 0 4px 20px rgba(9,9,11,0.02);
    }
    .report-stat-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 18px; margin-bottom: 32px;
    }
    .report-stat {
        background: var(--card); border: 1px solid var(--border); border-radius: 12px;
        padding: 24px; transition: transform 0.2s, box-shadow 0.2s;
        box-shadow: 0 4px 20px rgba(9,9,11,0.02);
    }
    .report-stat:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(124,58,237,0.08); }
    .report-stat .stat-icon {
        width: 40px; height: 40px; border-radius: 10px; display: flex;
        align-items: center; justify-content: center; margin-bottom: 14px;
    }
    .report-stat .stat-value { font-size: 32px; font-weight: 800; letter-spacing: -1px; }
    .report-stat .stat-label { font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-top: 6px; }
    .tab-bar { display: flex; gap: 6px; margin-bottom: 24px; }
    .tab-bar a {
        padding: 8px 20px; border-radius: 8px; font-size: 13px; font-weight: 600;
        text-decoration: none; color: var(--text-muted); background: var(--card);
        border: 1px solid var(--border); transition: all 0.2s;
    }
    .tab-bar a:hover { color: var(--accent); border-color: var(--accent); }
    .tab-bar a.active { background: var(--accent); color: #fff; border-color: var(--accent); }
</style>

<!-- Date Filter Bar -->
<div class="report-filter">
    <form method="GET" action="" style="display:flex; align-items:center; gap:14px; flex-wrap:wrap; width:100%;">
        <div style="display:flex; align-items:center; gap:8px;">
            <i data-lucide="calendar" style="width:18px;height:18px;stroke:var(--accent);"></i>
            <span style="font-size:13px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px;">Date Range</span>
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            <label style="margin:0; font-size:12px;">From</label>
            <input type="date" name="from" value="<?= htmlspecialchars($fromDate) ?>" class="form-control" style="width:160px; margin:0; padding:9px 12px;">
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            <label style="margin:0; font-size:12px;">To</label>
            <input type="date" name="to" value="<?= htmlspecialchars($toDate) ?>" class="form-control" style="width:160px; margin:0; padding:9px 12px;">
        </div>
        <button type="submit" class="btn" style="padding:10px 20px; font-size:13px; gap:6px;">
            <i data-lucide="filter" style="width:14px;height:14px;"></i> Filter
        </button>
        <?php if ($hasFilter): ?>
        <a href="myReports.php" class="btn-outline" style="padding:10px 20px; font-size:13px; display:flex; align-items:center; gap:6px;">
            <i data-lucide="x" style="width:14px;height:14px;"></i> Reset
        </a>
        <span style="font-size:12px; color:var(--accent); font-weight:600;">
            <?= date('d M Y', strtotime($fromDate)) ?> — <?= date('d M Y', strtotime($toDate)) ?>
        </span>
        <?php endif; ?>
        <button type="button" onclick="exportReportToPDF('reader-report-content', 'My_Activity_Report', 'Reader Activity & Purchase Summary')" class="btn no-print" style="padding:10px 20px; font-size:13px; gap:6px; background:#10b981; color:#ffffff; border:none; margin-left:auto; cursor:pointer;">
            <i data-lucide="download-cloud" style="width:14px;height:14px;"></i> Download PDF Report
        </button>
    </form>
</div>

<div id="reader-report-content">
<!-- Stats -->
<div class="report-stat-grid">
    <div class="report-stat">
        <div class="stat-icon" style="background:rgba(124,58,237,0.1);">
            <i data-lucide="book-open" style="width:20px;height:20px;stroke:#7c3aed;"></i>
        </div>
        <div class="stat-value" style="color:var(--accent);"><?= $totalPurchased ?></div>
        <div class="stat-label"><?= $hasFilter ? 'Books Read (Filtered)' : 'Total Books Read' ?></div>
    </div>
    <div class="report-stat">
        <div class="stat-icon" style="background:rgba(16,185,129,0.1);">
            <i data-lucide="download" style="width:20px;height:20px;stroke:#10b981;"></i>
        </div>
        <div class="stat-value" style="color:#10b981;"><?= $totalDownloaded ?></div>
        <div class="stat-label"><?= $hasFilter ? 'Downloads (Filtered)' : 'Total Downloads' ?></div>
    </div>
    <div class="report-stat">
        <div class="stat-icon" style="background:rgba(245,158,11,0.1);">
            <i data-lucide="indian-rupee" style="width:20px;height:20px;stroke:#f59e0b;"></i>
        </div>
        <div class="stat-value" style="color:#f59e0b;">₹<?= number_format($totalSpent) ?></div>
        <div class="stat-label"><?= $hasFilter ? 'Spent (Filtered)' : 'Total Spent' ?></div>
    </div>
</div>

<!-- Books Read -->
<div class="glass-card">
    <h2 style="font-size:16px; font-weight:700; margin-bottom:20px; display:flex; align-items:center; gap:8px;">
        <i data-lucide="book-open" style="width:18px;height:18px;stroke:var(--accent);"></i> Books Read
    </h2>
    <?php if ($booksRead): ?>
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Book Name</th>
                    <th>Author</th>
                    <th>Type</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
            <?php $i=1; foreach ($booksRead as $b): ?>
                <tr>
                    <td style="color:var(--text-muted);"><?= $i++ ?></td>
                    <td style="font-weight:600;"><?= htmlspecialchars($b['bname']) ?></td>
                    <td><?= htmlspecialchars($b['AuthorName']) ?></td>
                    <td>
                        <?php if (strtolower($b['is_paid']) == 'paid'): ?>
                            <span class="badge badge-paid">Paid</span>
                        <?php else: ?>
                            <span class="badge badge-free">Free</span>
                        <?php endif; ?>
                    </td>
                    <td style="color:var(--text-muted); font-size:13px;"><?= date('d M Y', strtotime($b['purchase_date'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div style="padding:40px; text-align:center; color:var(--text-muted);">
        <i data-lucide="book-x" style="width:36px;height:36px;margin-bottom:10px;opacity:0.4;"></i>
        <div style="font-size:14px;">No books read <?= $hasFilter ? 'in this period' : 'yet' ?>.</div>
    </div>
    <?php endif; ?>
</div>

<!-- Downloads -->
<div class="glass-card">
    <h2 style="font-size:16px; font-weight:700; margin-bottom:20px; display:flex; align-items:center; gap:8px;">
        <i data-lucide="download" style="width:18px;height:18px;stroke:#10b981;"></i> Downloads
    </h2>
    <?php if ($downloads): ?>
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Book Name</th>
                    <th>Author</th>
                    <th>Download Date</th>
                </tr>
            </thead>
            <tbody>
            <?php $i=1; foreach ($downloads as $d): ?>
                <tr>
                    <td style="color:var(--text-muted);"><?= $i++ ?></td>
                    <td style="font-weight:600;"><?= htmlspecialchars($d['bname']) ?></td>
                    <td><?= htmlspecialchars($d['AuthorName']) ?></td>
                    <td style="color:var(--text-muted); font-size:13px;"><?= date('d M Y, h:i A', strtotime($d['download_date'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div style="padding:40px; text-align:center; color:var(--text-muted);">
        <i data-lucide="download-cloud" style="width:36px;height:36px;margin-bottom:10px;opacity:0.4;"></i>
        <div style="font-size:14px;">No downloads <?= $hasFilter ? 'in this period' : 'yet' ?>.</div>
    </div>
    <?php endif; ?>
</div>

<!-- Payment History -->
<div class="glass-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
        <h2 style="font-size:16px; font-weight:700; display:flex; align-items:center; gap:8px; margin:0;">
            <i data-lucide="credit-card" style="width:18px;height:18px;stroke:#f59e0b;"></i> Payment History
        </h2>
        <div class="tab-bar" style="margin:0;">
            <?php
            $baseUrl = 'myReports.php';
            if ($hasFilter) $baseUrl .= "?from=$fromDate&to=$toDate";
            ?>
            <a href="<?= $hasFilter ? "myReports.php?from=$fromDate&to=$toDate" : "myReports.php" ?>" class="<?= !$showAllPayments ? 'active' : '' ?>">
                <?= $hasFilter ? 'Date Range' : 'All Time' ?>
            </a>
            <?php if ($hasFilter): ?>
            <a href="myReports.php?from=<?= $fromDate ?>&to=<?= $toDate ?>&all_payments=1" class="<?= $showAllPayments ? 'active' : '' ?>">All Time</a>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($payments): ?>
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Book Name</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Transaction ID</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
            <?php $i=1; foreach ($payments as $p): ?>
                <tr>
                    <td style="color:var(--text-muted);"><?= $i++ ?></td>
                    <td style="font-weight:600;"><?= htmlspecialchars($p['bname']) ?></td>
                    <td style="color:#10b981; font-weight:700;">₹<?= $p['amount'] ?></td>
                    <td><span class="badge badge-paid"><?= htmlspecialchars($p['payment_method']) ?></span></td>
                    <td style="font-size:12px; color:var(--text-muted); font-family:monospace;"><?= htmlspecialchars($p['transaction_id']) ?></td>
                    <td style="color:var(--text-muted); font-size:13px;"><?= date('d M Y, h:i A', strtotime($p['payment_date'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div style="padding:40px; text-align:center; color:var(--text-muted);">
        <i data-lucide="wallet" style="width:36px;height:36px;margin-bottom:10px;opacity:0.4;"></i>
        <div style="font-size:14px;">No payments <?= ($hasFilter && !$showAllPayments) ? 'in this period' : 'recorded yet' ?>.</div>
    </div>
    <?php endif; ?>
</div>
</div>

<?php include("readerFooter.php"); ?>
