<?php
require('../config/autoload.php');
include("header.php");
include("dbcon.php");

// --- Date Filter ---
$fromDate = isset($_GET['from']) ? $_GET['from'] : '';
$toDate   = isset($_GET['to'])   ? $_GET['to']   : '';
$hasFilter = ($fromDate !== '' && $toDate !== '');

$dateCondAuthors = $hasFilter ? "AND ar.status >= 1" : "";
$dateCondBooks   = $hasFilter ? "AND b.AddedDate BETWEEN '$fromDate' AND '$toDate'" : "";
$dateCondReaders = $hasFilter ? "AND rr.status >= 1" : "";
$dateCondPurchases = $hasFilter ? "AND p.purchase_date BETWEEN '$fromDate' AND '$toDate 23:59:59'" : "";
$dateCondDownloads = $hasFilter ? "AND d.download_date BETWEEN '$fromDate' AND '$toDate 23:59:59'" : "";

// === SUMMARY STATS ===
$statAuthors = $conn->query("SELECT COUNT(*) as cnt FROM authorregistration WHERE status=2")->fetch_assoc()['cnt'];
$statBooks   = $conn->query("SELECT COUNT(*) as cnt FROM books WHERE status=1 " . ($hasFilter ? "AND AddedDate BETWEEN '$fromDate' AND '$toDate'" : ""))->fetch_assoc()['cnt'];
$statReaders = $conn->query("SELECT COUNT(*) as cnt FROM readerregistration WHERE status=1")->fetch_assoc()['cnt'];
$revResult   = $conn->query("SELECT COALESCE(SUM(amount),0) as rev FROM purchases WHERE payment_status='completed' " . ($hasFilter ? "AND purchase_date BETWEEN '$fromDate' AND '$toDate 23:59:59'" : ""));
$statRevenue = $revResult->fetch_assoc()['rev'];

// === AUTHORS REPORT ===
$authorReport = $conn->query("
    SELECT ar.authid, ar.username, ar.email,
           COUNT(DISTINCT b.bid) as total_books,
           COALESCE(SUM(CASE WHEN p.payment_status='completed' THEN 1 ELSE 0 END),0) as total_sales,
           COALESCE(SUM(CASE WHEN p.payment_status='completed' THEN p.amount ELSE 0 END),0) as total_earnings
    FROM authorregistration ar
    LEFT JOIN books b ON b.AuthorName = ar.username AND b.status=1 $dateCondBooks
    LEFT JOIN purchases p ON p.bid = b.bid $dateCondPurchases
    WHERE ar.status = 2
    GROUP BY ar.authid
    ORDER BY total_earnings DESC
");

// === BOOKS REPORT ===
$booksReport = $conn->query("
    SELECT b.bid, b.bname, b.AuthorName, c.cname, b.is_paid, b.price, b.AddedDate,
           COUNT(DISTINCT d.downloadid) as download_count,
           COUNT(DISTINCT p.purchaseid) as purchase_count
    FROM books b
    LEFT JOIN category c ON b.cid = c.cid
    LEFT JOIN downloads d ON d.bid = b.bid $dateCondDownloads
    LEFT JOIN purchases p ON p.bid = b.bid AND p.payment_status='completed' $dateCondPurchases
    WHERE b.status=1 " . ($hasFilter ? "AND b.AddedDate BETWEEN '$fromDate' AND '$toDate'" : "") . "
    GROUP BY b.bid
    ORDER BY b.AddedDate DESC
");

// === READERS REPORT ===
$readersReport = $conn->query("
    SELECT rr.userId, rr.username, rr.email,
           COUNT(DISTINCT p.purchaseid) as books_purchased,
           COUNT(DISTINCT d.downloadid) as books_downloaded,
           COALESCE(SUM(CASE WHEN p.payment_status='completed' THEN p.amount ELSE 0 END),0) as total_spent
    FROM readerregistration rr
    LEFT JOIN purchases p ON p.userId = rr.userId $dateCondPurchases
    LEFT JOIN downloads d ON d.userId = rr.userId $dateCondDownloads
    WHERE rr.status = 1
    GROUP BY rr.userId
    ORDER BY total_spent DESC
");
?>

<!-- Date Filter Bar -->
<div class="section" style="padding:18px 24px; margin-bottom:24px;">
    <form method="GET" action="" style="display:flex; align-items:center; gap:14px; flex-wrap:wrap; width:100%;">
        <div style="display:flex; align-items:center; gap:8px;">
            <i data-lucide="calendar" style="width:18px;height:18px;stroke:var(--text-muted);"></i>
            <span style="font-size:13px; font-weight:600; color:var(--text-muted);">DATE RANGE</span>
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            <label style="margin:0; font-size:12px;">From</label>
            <input type="date" name="from" value="<?= htmlspecialchars($fromDate) ?>" class="form-control" style="width:160px; margin:0; padding:7px 10px;">
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            <label style="margin:0; font-size:12px;">To</label>
            <input type="date" name="to" value="<?= htmlspecialchars($toDate) ?>" class="form-control" style="width:160px; margin:0; padding:7px 10px;">
        </div>
        <button type="submit" class="btn btn-primary btn-sm" style="display:flex; align-items:center; gap:5px;">
            <i data-lucide="filter" style="width:14px;height:14px;"></i> Filter
        </button>
        <?php if ($hasFilter): ?>
        <a href="Reports.php" class="btn btn-secondary btn-sm" style="display:flex; align-items:center; gap:5px;">
            <i data-lucide="x" style="width:14px;height:14px;"></i> Reset
        </a>
        <span style="font-size:12px; color:var(--accent); font-weight:500;">
            Showing: <?= date('d M Y', strtotime($fromDate)) ?> — <?= date('d M Y', strtotime($toDate)) ?>
        </span>
        <?php endif; ?>
        <button type="button" onclick="exportReportToPDF('admin-report-content', 'Admin_Executive_Report', 'Admin System Executive Report')" class="btn btn-secondary btn-sm no-print" style="display:flex; align-items:center; gap:6px; background:#10b981; color:#ffffff; border:none; margin-left:auto; cursor:pointer;">
            <i data-lucide="download-cloud" style="width:14px;height:14px;"></i> Download PDF Report
        </button>
    </form>
</div>

<div id="admin-report-content">
<!-- Stat Cards -->
<div class="stat-grid">
    <div class="stat-card sc-blue">
        <div class="sc-icon"><i data-lucide="users" style="width:20px;height:20px;stroke:currentColor;"></i></div>
        <div class="sc-value"><?= $statAuthors ?></div>
        <div class="sc-label">Approved Authors</div>
    </div>
    <div class="stat-card sc-green">
        <div class="sc-icon"><i data-lucide="book-open" style="width:20px;height:20px;stroke:currentColor;"></i></div>
        <div class="sc-value"><?= $statBooks ?></div>
        <div class="sc-label"><?= $hasFilter ? 'Books (Filtered)' : 'Total Books' ?></div>
    </div>
    <div class="stat-card sc-amber">
        <div class="sc-icon"><i data-lucide="user" style="width:20px;height:20px;stroke:currentColor;"></i></div>
        <div class="sc-value"><?= $statReaders ?></div>
        <div class="sc-label">Active Readers</div>
    </div>
    <div class="stat-card sc-green">
        <div class="sc-icon"><i data-lucide="indian-rupee" style="width:20px;height:20px;stroke:currentColor;"></i></div>
        <div class="sc-value">₹<?= number_format($statRevenue) ?></div>
        <div class="sc-label"><?= $hasFilter ? 'Revenue (Filtered)' : 'Total Revenue' ?></div>
    </div>
</div>

<!-- Authors Report -->
<div class="section">
    <h2 style="display:flex; align-items:center; gap:8px;">
        <i data-lucide="pen-tool" style="width:18px;height:18px;stroke:var(--accent);"></i> Authors Report
    </h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Author</th>
                    <th>Email</th>
                    <th>Books</th>
                    <th>Sales</th>
                    <th>Earnings</th>
                </tr>
            </thead>
            <tbody>
            <?php $i=1; while ($row = $authorReport->fetch_assoc()): ?>
                <tr>
                    <td style="color:var(--text-muted);"><?= $i++ ?></td>
                    <td style="font-weight:500;"><?= htmlspecialchars($row['username']) ?></td>
                    <td style="font-size:12px; color:var(--text-muted);"><?= htmlspecialchars($row['email']) ?></td>
                    <td><span class="badge badge-active"><?= $row['total_books'] ?></span></td>
                    <td><?= $row['total_sales'] ?></td>
                    <td style="color:#10b981; font-weight:600;">₹<?= number_format($row['total_earnings']) ?></td>
                </tr>
            <?php endwhile; ?>
            <?php if ($i === 1): ?>
                <tr><td colspan="6" style="text-align:center; padding:30px; color:var(--text-muted);">No authors found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Books Report -->
<div class="section">
    <h2 style="display:flex; align-items:center; gap:8px;">
        <i data-lucide="book-open" style="width:18px;height:18px;stroke:var(--accent);"></i> Books Report
    </h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Book Name</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th>Price</th>
                    <th>Downloads</th>
                    <th>Purchases</th>
                    <th>Added Date</th>
                </tr>
            </thead>
            <tbody>
            <?php $i=1; while ($row = $booksReport->fetch_assoc()): ?>
                <tr>
                    <td style="color:var(--text-muted);"><?= $i++ ?></td>
                    <td style="font-weight:500;"><?= htmlspecialchars($row['bname']) ?></td>
                    <td><?= htmlspecialchars($row['AuthorName']) ?></td>
                    <td><span class="badge badge-pending"><?= htmlspecialchars($row['cname'] ?? 'N/A') ?></span></td>
                    <td>
                        <?php if (strtolower($row['is_paid']) == 'paid'): ?>
                            <span class="badge badge-paid">Paid</span>
                        <?php else: ?>
                            <span class="badge badge-free">Free</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-weight:600;">₹<?= $row['price'] ?></td>
                    <td><?= $row['download_count'] ?></td>
                    <td><?= $row['purchase_count'] ?></td>
                    <td style="font-size:12px; color:var(--text-muted);"><?= date('d M Y', strtotime($row['AddedDate'])) ?></td>
                </tr>
            <?php endwhile; ?>
            <?php if ($i === 1): ?>
                <tr><td colspan="9" style="text-align:center; padding:30px; color:var(--text-muted);">No books found in this range.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Readers Report -->
<div class="section">
    <h2 style="display:flex; align-items:center; gap:8px;">
        <i data-lucide="users" style="width:18px;height:18px;stroke:var(--accent);"></i> Readers Report
    </h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Reader</th>
                    <th>Email</th>
                    <th>Purchased</th>
                    <th>Downloaded</th>
                    <th>Total Spent</th>
                </tr>
            </thead>
            <tbody>
            <?php $i=1; while ($row = $readersReport->fetch_assoc()): ?>
                <tr>
                    <td style="color:var(--text-muted);"><?= $i++ ?></td>
                    <td style="font-weight:500;"><?= htmlspecialchars($row['username']) ?></td>
                    <td style="font-size:12px; color:var(--text-muted);"><?= htmlspecialchars($row['email']) ?></td>
                    <td><span class="badge badge-active"><?= $row['books_purchased'] ?></span></td>
                    <td><span class="badge badge-pending"><?= $row['books_downloaded'] ?></span></td>
                    <td style="color:#10b981; font-weight:600;">₹<?= number_format($row['total_spent']) ?></td>
                </tr>
            <?php endwhile; ?>
            <?php if ($i === 1): ?>
                <tr><td colspan="6" style="text-align:center; padding:30px; color:var(--text-muted);">No readers found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div>

<?php include("footer.php"); ?>
