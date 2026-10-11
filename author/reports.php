<?php
require('../config/autoload.php');
if (!isset($_SESSION['authid'])) { header('location:login.php'); exit(); }

$authid = $_SESSION['authid'];
$authorName = $_SESSION['authname'];
$dao = new DataAccess();

include("header.php");

$safeAuthorName = addslashes($authorName);

// --- Date Filter ---
$fromDate  = isset($_GET['from']) ? $_GET['from'] : '';
$toDate    = isset($_GET['to'])   ? $_GET['to']   : '';
$hasFilter = ($fromDate !== '' && $toDate !== '');

$datePurchase = $hasFilter ? "AND p.purchase_date BETWEEN '$fromDate' AND '$toDate 23:59:59'" : "";
$dateDownload = $hasFilter ? "AND d.download_date BETWEEN '$fromDate' AND '$toDate 23:59:59'" : "";

// === SUMMARY STATS ===
$readerStat = $dao->query("SELECT COUNT(DISTINCT p.userId) as cnt 
                           FROM purchases p JOIN books b ON p.bid=b.bid 
                           WHERE b.AuthorName='$safeAuthorName' AND p.payment_status='completed' $datePurchase");
$totalReaders = $readerStat ? $readerStat[0]['cnt'] : 0;

$dlStat = $dao->query("SELECT COUNT(*) as cnt 
                       FROM downloads d JOIN books b ON d.bid=b.bid 
                       WHERE b.AuthorName='$safeAuthorName' $dateDownload");
$totalDownloads = $dlStat ? $dlStat[0]['cnt'] : 0;

$revStat = $dao->query("SELECT COALESCE(SUM(p.amount),0) as total 
                        FROM purchases p JOIN books b ON p.bid=b.bid 
                        WHERE b.AuthorName='$safeAuthorName' AND p.payment_status='completed' $datePurchase");
$totalRevenue = $revStat ? $revStat[0]['total'] : 0;

$bookStat = $dao->query("SELECT COUNT(*) as cnt FROM books WHERE AuthorName='$safeAuthorName' AND status=1" 
                        . ($hasFilter ? " AND AddedDate BETWEEN '$fromDate' AND '$toDate'" : ""));
$totalBooks = $bookStat ? $bookStat[0]['cnt'] : 0;

// === READER ENGAGEMENT ===
$engagement = $dao->query("
    SELECT r.username, b.bname, p.amount, p.purchase_date
    FROM purchases p
    JOIN books b ON p.bid = b.bid
    JOIN readerregistration r ON p.userId = r.userId
    WHERE b.AuthorName='$safeAuthorName' AND p.payment_status='completed' $datePurchase
    ORDER BY p.purchase_date DESC
");

// === BOOK PERFORMANCE ===
$bookPerf = $dao->query("
    SELECT b.bid, b.bname, b.is_paid, b.price, b.AddedDate,
           (SELECT COUNT(*) FROM purchases p2 WHERE p2.bid=b.bid AND p2.payment_status='completed' " . ($hasFilter ? "AND p2.purchase_date BETWEEN '$fromDate' AND '$toDate 23:59:59'" : "") . ") as purchase_count,
           (SELECT COUNT(*) FROM downloads d2 WHERE d2.bid=b.bid " . ($hasFilter ? "AND d2.download_date BETWEEN '$fromDate' AND '$toDate 23:59:59'" : "") . ") as download_count,
           (SELECT COALESCE(SUM(p3.amount),0) FROM purchases p3 WHERE p3.bid=b.bid AND p3.payment_status='completed' " . ($hasFilter ? "AND p3.purchase_date BETWEEN '$fromDate' AND '$toDate 23:59:59'" : "") . ") as revenue
    FROM books b
    WHERE b.AuthorName='$safeAuthorName' AND b.status=1
    ORDER BY revenue DESC
");
?>

<!-- Date Filter Bar -->
<div class="glass-card" style="padding:18px 24px; margin-bottom:28px;">
    <form method="GET" action="" style="display:flex; align-items:center; gap:14px; flex-wrap:wrap; width:100%;">
        <div style="display:flex; align-items:center; gap:8px;">
            <i data-lucide="calendar" style="width:18px;height:18px;stroke:var(--accent);"></i>
            <span style="font-size:12px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px;">Date Range</span>
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            <label style="margin:0; font-size:12px;">From</label>
            <input type="date" name="from" value="<?= htmlspecialchars($fromDate) ?>" class="form-control" style="width:160px; margin:0; padding:8px 10px;">
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            <label style="margin:0; font-size:12px;">To</label>
            <input type="date" name="to" value="<?= htmlspecialchars($toDate) ?>" class="form-control" style="width:160px; margin:0; padding:8px 10px;">
        </div>
        <button type="submit" class="btn" style="padding:9px 20px; font-size:13px; gap:6px;">
            <i data-lucide="filter" style="width:14px;height:14px;"></i> Filter
        </button>
        <?php if ($hasFilter): ?>
        <a href="reports.php" class="btn" style="padding:9px 20px; font-size:13px; gap:6px; background:rgba(255,255,255,0.06); border:1px solid var(--border);">
            <i data-lucide="x" style="width:14px;height:14px;"></i> Reset
        </a>
        <span style="font-size:12px; color:var(--accent); font-weight:600;">
            <?= date('d M Y', strtotime($fromDate)) ?> — <?= date('d M Y', strtotime($toDate)) ?>
        </span>
        <?php endif; ?>
        <button type="button" onclick="exportReportToPDF('author-report-content', 'Author_Performance_Report', 'Author Performance & Sales Report')" class="btn no-print" style="padding:9px 20px; font-size:13px; gap:6px; background:#10b981; color:#ffffff; border:none; margin-left:auto; cursor:pointer;">
            <i data-lucide="download-cloud" style="width:14px;height:14px;"></i> Download PDF Report
        </button>
    </form>
</div>

<div id="author-report-content">
<!-- Stats Grid -->
<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:18px; margin-bottom:32px;">
    <div style="background:var(--card); border:1px solid var(--border); border-radius:12px; padding:24px;">
        <div style="font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:12px;">Readers</div>
        <div style="font-size:36px; font-weight:700; color:var(--accent);"><?= $totalReaders ?></div>
        <div style="margin-top:8px; font-size:12px; color:var(--accent);">👥 <?= $hasFilter ? 'In Period' : 'Total Unique' ?></div>
    </div>
    <div style="background:var(--card); border:1px solid var(--border); border-radius:12px; padding:24px;">
        <div style="font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:12px;">Downloads</div>
        <div style="font-size:36px; font-weight:700; color:#3b82f6;"><?= $totalDownloads ?></div>
        <div style="margin-top:8px; font-size:12px; color:#3b82f6;">📥 <?= $hasFilter ? 'In Period' : 'All Time' ?></div>
    </div>
    <div style="background:var(--card); border:1px solid var(--border); border-radius:12px; padding:24px;">
        <div style="font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:12px;">Revenue</div>
        <div style="font-size:36px; font-weight:700; color:#10b981;">₹<?= number_format($totalRevenue) ?></div>
        <div style="margin-top:8px; font-size:12px; color:#10b981;">💰 <?= $hasFilter ? 'In Period' : 'Total Earned' ?></div>
    </div>
    <div style="background:var(--card); border:1px solid var(--border); border-radius:12px; padding:24px;">
        <div style="font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:12px;">Books</div>
        <div style="font-size:36px; font-weight:700; color:#f59e0b;"><?= $totalBooks ?></div>
        <div style="margin-top:8px; font-size:12px; color:#f59e0b;">📘 <?= $hasFilter ? 'Added in Period' : 'My Books' ?></div>
    </div>
</div>

<!-- Reader Engagement -->
<div class="glass-card">
    <h2 style="font-size:16px; font-weight:600; margin-bottom:20px; display:flex; align-items:center; gap:8px;">
        <i data-lucide="users" style="width:18px;height:18px;stroke:var(--accent);"></i> Reader Engagement
    </h2>
    <?php if (!empty($engagement)): ?>
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Reader</th>
                    <th>Book Purchased</th>
                    <th>Amount</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
            <?php $i=1; foreach ($engagement as $e): ?>
                <tr>
                    <td style="color:var(--text-muted);"><?= $i++ ?></td>
                    <td style="font-weight:500;"><?= htmlspecialchars($e['username']) ?></td>
                    <td><?= htmlspecialchars($e['bname']) ?></td>
                    <td style="color:#10b981; font-weight:600;">₹<?= $e['amount'] ?></td>
                    <td style="color:var(--text-muted); font-size:12px;"><?= date('d M Y, h:i A', strtotime($e['purchase_date'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div style="padding:40px; text-align:center; color:var(--text-muted);">
        <i data-lucide="user-x" style="width:36px;height:36px;margin-bottom:10px;opacity:0.4;"></i>
        <div style="font-size:14px;">No reader purchases <?= $hasFilter ? 'in this period' : 'yet' ?>.</div>
    </div>
    <?php endif; ?>
</div>

<!-- Book Performance -->
<div class="glass-card">
    <h2 style="font-size:16px; font-weight:600; margin-bottom:20px; display:flex; align-items:center; gap:8px;">
        <i data-lucide="bar-chart-3" style="width:18px;height:18px;stroke:var(--accent);"></i> Book Performance
    </h2>
    <?php if (!empty($bookPerf)): ?>
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Book Name</th>
                    <th>Type</th>
                    <th>Purchases</th>
                    <th>Downloads</th>
                    <th>Revenue</th>
                    <th>Added Date</th>
                </tr>
            </thead>
            <tbody>
            <?php $i=1; foreach ($bookPerf as $bp): ?>
                <tr>
                    <td style="color:var(--text-muted);"><?= $i++ ?></td>
                    <td style="font-weight:500;"><?= htmlspecialchars($bp['bname']) ?></td>
                    <td>
                        <?php if (strtolower($bp['is_paid']) == 'paid'): ?>
                            <span class="badge badge-paid">Paid</span>
                        <?php else: ?>
                            <span class="badge badge-free">Free</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-weight:600;"><?= $bp['purchase_count'] ?></td>
                    <td><?= $bp['download_count'] ?></td>
                    <td style="color:#10b981; font-weight:600;">₹<?= number_format($bp['revenue']) ?></td>
                    <td style="color:var(--text-muted); font-size:12px;"><?= date('d M Y', strtotime($bp['AddedDate'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div style="padding:40px; text-align:center; color:var(--text-muted);">
        <i data-lucide="book-x" style="width:36px;height:36px;margin-bottom:10px;opacity:0.4;"></i>
        <div style="font-size:14px;">No books found<?= $hasFilter ? ' in this period' : '' ?>.</div>
    </div>
    <?php endif; ?>
</div>
</div>

<?php include("footer.php"); ?>
