<?php
require('../config/autoload.php');
if (!isset($_SESSION['reader_id'])) {
    header('location: readerLogin.php'); exit();
}
include("readerHeader.php");

$dao      = new DataAccess();
$readerId = $_SESSION['reader_id'];

$purchases = $dao->query("
    SELECT p.*, b.bname, b.AuthorName, b.CoverImage, b.story, py.payment_method, py.transaction_id as pay_txn
    FROM purchases p
    LEFT JOIN books b ON p.bid = b.bid
    LEFT JOIN payments py ON p.purchaseid = py.purchaseid
    WHERE p.userId='$readerId' AND p.status=1
    ORDER BY p.purchase_date DESC
");
?>
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
    <h2 style="margin:0; font-size:18px;">🛍️ My Purchases Report</h2>
    <button type="button" onclick="exportReportToPDF('reader-purchases-content', 'My_Purchases_Report', 'Reader Purchase History Report')" class="btn no-print" style="padding:9px 20px; font-size:13px; gap:6px; background:#10b981; color:#ffffff; border:none; cursor:pointer; display:flex; align-items:center;">
        <i data-lucide="download-cloud" style="width:14px;height:14px;"></i> Download PDF Report
    </button>
</div>

<div id="reader-purchases-content">
<div class="glass-card" style="padding:0; overflow:hidden;">
    <?php if ($purchases): ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Cover</th>
                <th>Book Details</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Date</th>
                <th class="no-print">Action</th>
            </tr>
        </thead>
        <tbody>
        <?php $i = 1; foreach ($purchases as $p): ?>
            <tr>
                <td><span style="color:var(--text-muted);"><?= $i++ ?></span></td>
                <td><img src="../uploads/<?= htmlspecialchars($p['CoverImage']) ?>" style="width:40px; height:55px; object-fit:cover; border-radius:6px;" onerror="this.src='../assets/img/placeholder.png'"/></td>
                <td>
                    <div style="font-weight:600; margin-bottom:4px;"><?= htmlspecialchars($p['bname']) ?></div>
                    <div style="font-size:12px; color:var(--text-muted);">By <?= htmlspecialchars($p['AuthorName']) ?></div>
                </td>
                <td><span class="badge badge-paid">₹<?= $p['amount'] ?></span></td>
                <td>
                    <div style="font-weight:500; font-size:13px;"><?= htmlspecialchars($p['pay_txn'] ? $p['payment_method'] : '-') ?></div>
                    <small style="color:var(--text-muted);"><?= htmlspecialchars($p['transaction_id']) ?></small>
                </td>
                <td style="color:var(--text-muted); font-size:13px;"><?= date('d M Y', strtotime($p['purchase_date'])) ?></td>
                <td class="no-print">
                    <div style="display:flex; gap:8px; flex-direction:column;">
                        <a href="downloadBook.php?bid=<?= $p['bid'] ?>" class="btn" style="padding:8px 16px; font-size:13px; text-align:center; display:flex; align-items:center; justify-content:center; gap:6px;">
                            <i data-lucide="download" style="width:14px;height:14px;"></i> Download Book
                        </a>
                        <a href="../uploadpdf/<?= htmlspecialchars($p['story']) ?>" target="_blank" class="btn-outline" style="padding:8px 16px; font-size:13px; text-align:center; display:flex; align-items:center; justify-content:center; gap:6px;">
                            <i data-lucide="book-open" style="width:14px;height:14px;"></i> Read Online
                        </a>
                        <a href="bookDetail.php?bid=<?= $p['bid'] ?>#review" class="btn-outline" style="padding:8px 16px; font-size:13px; text-align:center; display:flex; align-items:center; justify-content:center; gap:6px;">
                            <i data-lucide="star" style="width:14px;height:14px;"></i> Add Review
                        </a>
                        <a href="complaints.php?bid=<?= $p['bid'] ?>&type=Payment" class="btn-outline" style="padding:8px 16px; font-size:13px; text-align:center; border-color:rgba(239,68,68,0.4); color:#ef4444; display:flex; align-items:center; justify-content:center; gap:6px;" title="Report an issue with this purchase">
                            <i data-lucide="flag" style="width:13px;height:13px;"></i> Report Issue
                        </a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div style="padding:80px 40px; text-align:center; color:var(--text-muted);">
        <i data-lucide="shopping-bag" style="width:48px;height:48px;margin-bottom:12px;opacity:0.5;"></i>
        <div style="font-size:16px;font-weight:600;">No Purchases Yet</div>
        <div style="font-size:14px;margin-top:6px;margin-bottom:24px;">You have not purchased any premium books yet.</div>
        <a href="bookList.php?type=paid" class="btn">
            <i data-lucide="search" style="width:16px;height:16px;"></i> Explore Premium Library
        </a>
    </div>
    <?php endif; ?>
</div>
</div>
<?php include("readerFooter.php"); ?>
