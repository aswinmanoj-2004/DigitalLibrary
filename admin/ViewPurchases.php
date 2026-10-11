<?php
require('../config/autoload.php');
include("header.php");
include("dbcon.php");

$totalRevenue   = $conn->query("SELECT SUM(amount) as total FROM purchases WHERE payment_status='completed'")->fetch_assoc()['total'] ?? 0;
$totalCompleted = $conn->query("SELECT COUNT(*) as cnt FROM purchases WHERE payment_status='completed'")->fetch_assoc()['cnt'];
$totalPending   = $conn->query("SELECT COUNT(*) as cnt FROM purchases WHERE payment_status='pending'")->fetch_assoc()['cnt'];
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
    <h2 style="margin:0; font-size:18px;">🛒 All Purchases Report</h2>
    <button type="button" onclick="exportReportToPDF('purchases-report-content', 'Admin_Purchases_Report', 'Admin All Purchases Report')" class="btn btn-secondary btn-sm no-print" style="display:flex; align-items:center; gap:6px; background:#10b981; color:#ffffff; border:none; padding:8px 16px; border-radius:6px; font-weight:600; cursor:pointer;">
        <i data-lucide="download-cloud" style="width:14px;height:14px;"></i> Download PDF Report
    </button>
</div>

<div id="purchases-report-content">
<!-- Summary mini-stats -->
<div class="stat-grid" style="grid-template-columns:repeat(auto-fill,minmax(180px,1fr));margin-bottom:24px;">
    <div class="stat-card sc-green">
        <div class="sc-icon">₹</div>
        <div class="sc-value">₹<?= number_format($totalRevenue) ?></div>
        <div class="sc-label">Total Revenue</div>
    </div>
    <div class="stat-card sc-blue">
        <div class="sc-icon">✅</div>
        <div class="sc-value"><?= $totalCompleted ?></div>
        <div class="sc-label">Completed Purchases</div>
    </div>
    <div class="stat-card sc-amber">
        <div class="sc-icon">⏳</div>
        <div class="sc-value"><?= $totalPending ?></div>
        <div class="sc-label">Pending Payments</div>
    </div>
</div>

<div class="section">
    <div class="table-wrap">
        <table>
            <tr>
                <th>Sl</th>
                <th>Purchase ID</th>
                <th>Reader</th>
                <th>Book</th>
                <th>Author</th>
                <th>Amount (₹)</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
            <?php
            $sql = "SELECT p.purchaseid, r.username AS reader, b.bname AS book,
                           b.AuthorName, p.amount, p.payment_status, p.purchase_date
                    FROM purchases p
                    LEFT JOIN readerregistration r ON p.userId = r.userId
                    LEFT JOIN books b              ON p.bid    = b.bid
                    ORDER BY p.purchase_date DESC";
            $result = $conn->query($sql);
            $i = 1;
            while ($row = $result->fetch_assoc()):
                $ps = strtolower($row['payment_status']);
                $psClass = $ps === 'completed' ? 'badge-active' : ($ps === 'pending' ? 'badge-open' : 'badge-banned');
            ?>
            <tr>
                <td><?= $i++ ?></td>
                <td style="color:var(--text-muted);">#<?= $row['purchaseid'] ?></td>
                <td><?= htmlspecialchars($row['reader'] ?? 'Unknown') ?></td>
                <td><?= htmlspecialchars($row['book']   ?? 'Unknown') ?></td>
                <td style="color:var(--text-muted);font-size:12.5px;"><?= htmlspecialchars($row['AuthorName'] ?? '—') ?></td>
                <td style="font-weight:600;color:var(--success);">₹<?= number_format($row['amount']) ?></td>
                <td><span class="badge <?= $psClass ?>"><?= ucfirst($ps) ?></span></td>
                <td style="font-size:12px;color:var(--text-muted);"><?= date('d M Y, h:i A', strtotime($row['purchase_date'])) ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>
</div>

<?php include("footer.php"); ?>
