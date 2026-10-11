<?php
require('../config/autoload.php');
if (!isset($_SESSION['authid'])) { header('location:login.php'); exit(); }

$authid = $_SESSION['authid'];
$authorName = $_SESSION['authname'];
$dao = new DataAccess();

include("header.php");

$safeAuthorName = addslashes($authorName);

// Analytics
$stats = $dao->query("SELECT SUM(p.amount) as total_rev, COUNT(p.purchaseid) as total_sales 
                      FROM purchases p 
                      JOIN books b ON p.bid = b.bid 
                      WHERE b.AuthorName = '$safeAuthorName' AND p.payment_status='completed'");

$totalRev = $stats[0]['total_rev'] ?? 0;
$totalSales = $stats[0]['total_sales'] ?? 0;

$pendingStats = $dao->query("SELECT COUNT(p.purchaseid) as pending_sales 
                             FROM purchases p 
                             JOIN books b ON p.bid = b.bid 
                             WHERE b.AuthorName = '$safeAuthorName' AND p.payment_status='pending'");
$pendingSales = $pendingStats[0]['pending_sales'] ?? 0;
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
    <h2 style="margin:0; font-size:18px;">💰 Payment & Sales Details</h2>
    <button type="button" onclick="exportReportToPDF('author-payments-content', 'Author_Payment_Details', 'Author Payment & Earnings Report')" class="btn no-print" style="padding:9px 20px; font-size:13px; gap:6px; background:#10b981; color:#ffffff; border:none; cursor:pointer; display:flex; align-items:center;">
        <i data-lucide="download-cloud" style="width:14px;height:14px;"></i> Download PDF Report
    </button>
</div>

<div id="author-payments-content">
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:32px;">
    <div style="background:var(--card);border:1px solid var(--border);border-radius:12px;padding:24px;">
        <div style="font-size:12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:12px;">Total Earnings</div>
        <div style="font-size:36px;font-weight:700;color:#10b981;">₹<?= number_format($totalRev) ?></div>
        <div style="margin-top:10px;font-size:12px;color:#10b981;">💰 Completed Payments</div>
    </div>
    <div style="background:var(--card);border:1px solid var(--border);border-radius:12px;padding:24px;">
        <div style="font-size:12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:12px;">Books Sold</div>
        <div style="font-size:36px;font-weight:700;color:var(--accent);"><?= $totalSales ?></div>
        <div style="margin-top:10px;font-size:12px;color:var(--accent);">📘 Total Copies</div>
    </div>
    <div style="background:var(--card);border:1px solid var(--border);border-radius:12px;padding:24px;">
        <div style="font-size:12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:12px;">Pending Transactions</div>
        <div style="font-size:36px;font-weight:700;color:#f59e0b;"><?= $pendingSales ?></div>
        <div style="margin-top:10px;font-size:12px;color:#f59e0b;">⏳ Awaiting Payment</div>
    </div>
</div>

<div class="glass-card">
    <h2 style="font-size:16px;font-weight:600;margin-bottom:20px;">Purchase History</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Book Name</th>
                    <th>Reader Name</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $purchases = $dao->query("SELECT p.purchaseid, b.bname, r.username, p.amount, p.payment_status, p.purchase_date 
                                      FROM purchases p
                                      JOIN books b ON p.bid = b.bid
                                      JOIN readerregistration r ON p.userId = r.userId
                                      WHERE b.AuthorName = '$safeAuthorName'
                                      ORDER BY p.purchase_date DESC");
            if (!empty($purchases)):
                foreach ($purchases as $p):
            ?>
            <tr>
                <td style="color:var(--text-muted);">#<?= $p['purchaseid'] ?></td>
                <td style="font-weight:500;"><?= htmlspecialchars($p['bname']) ?></td>
                <td><?= htmlspecialchars($p['username']) ?></td>
                <td style="color:#10b981;font-weight:600;">₹<?= $p['amount'] ?></td>
                <td>
                    <?php if (strtolower($p['payment_status']) == 'completed'): ?>
                        <span class="badge badge-free">Completed</span>
                    <?php else: ?>
                        <span class="badge badge-paid">Pending</span>
                    <?php endif; ?>
                </td>
                <td style="color:var(--text-muted);font-size:12px;"><?= date('M d, Y h:i A', strtotime($p['purchase_date'])) ?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="6" style="padding:30px;text-align:center;color:var(--text-muted);">No purchases made yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div>

<?php include("footer.php"); ?>
