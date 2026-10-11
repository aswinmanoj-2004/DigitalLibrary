<?php
require('../config/autoload.php');
if (!isset($_SESSION['reader_id'])) {
    header('location: readerLogin.php'); exit();
}
include("readerHeader.php");

$dao      = new DataAccess();
$readerId = $_SESSION['reader_id'];

$downloads = $dao->query("
    SELECT d.*, b.bname, b.AuthorName, b.CoverImage, b.story, c.cname
    FROM downloads d
    LEFT JOIN books b ON d.bid = b.bid
    LEFT JOIN category c ON b.cid = c.cid
    WHERE d.userId='$readerId' AND d.status=1
    ORDER BY d.download_date DESC
");
?>
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
    <h2 style="margin:0; font-size:18px;">📥 My Downloads Report</h2>
    <button type="button" onclick="exportReportToPDF('reader-downloads-content', 'My_Downloads_Report', 'Reader Downloads History Report')" class="btn no-print" style="padding:9px 20px; font-size:13px; gap:6px; background:#10b981; color:#ffffff; border:none; cursor:pointer; display:flex; align-items:center;">
        <i data-lucide="download-cloud" style="width:14px;height:14px;"></i> Download PDF Report
    </button>
</div>

<div id="reader-downloads-content">
<div class="glass-card" style="padding:0; overflow:hidden;">
    <?php if ($downloads): ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Cover</th>
                <th>Book Details</th>
                <th>Category</th>
                <th>Downloaded On</th>
                <th class="no-print">Action</th>
            </tr>
        </thead>
        <tbody>
        <?php $i = 1; foreach ($downloads as $d): ?>
            <tr>
                <td><span style="color:var(--text-muted);"><?= $i++ ?></span></td>
                <td><img src="../uploads/<?= htmlspecialchars($d['CoverImage']) ?>" style="width:40px; height:55px; object-fit:cover; border-radius:6px;" onerror="this.src='../assets/img/placeholder.png'"/></td>
                <td>
                    <div style="font-weight:600; margin-bottom:4px;"><?= htmlspecialchars($d['bname']) ?></div>
                    <div style="font-size:12px; color:var(--text-muted);">By <?= htmlspecialchars($d['AuthorName']) ?></div>
                </td>
                <td><span class="badge badge-free"><?= htmlspecialchars($d['cname']) ?></span></td>
                <td style="color:var(--text-muted); font-size:13px;"><?= date('d M Y, H:i', strtotime($d['download_date'])) ?></td>
                <td class="no-print">
                    <div style="display:flex; gap:8px; flex-direction:column;">
                        <a href="../uploadpdf/<?= htmlspecialchars($d['story']) ?>" target="_blank" class="btn" style="padding:8px 16px; font-size:13px; text-align:center; display:flex; align-items:center; justify-content:center; gap:6px;">
                            <i data-lucide="book-open" style="width:14px;height:14px;"></i> Read Book
                        </a>
                        <a href="bookDetail.php?bid=<?= $d['bid'] ?>#review" class="btn-outline" style="padding:8px 16px; font-size:13px; text-align:center; display:flex; align-items:center; justify-content:center; gap:6px;">
                            <i data-lucide="star" style="width:14px;height:14px;"></i> Add Review
                        </a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div style="padding:80px 40px; text-align:center; color:var(--text-muted);">
        <i data-lucide="download-cloud" style="width:48px;height:48px;margin-bottom:12px;opacity:0.5;"></i>
        <div style="font-size:16px;font-weight:600;">No Downloads Yet</div>
        <div style="font-size:14px;margin-top:6px;margin-bottom:24px;">You haven't downloaded any free books yet.</div>
        <a href="bookList.php?type=free" class="btn">
            <i data-lucide="search" style="width:16px;height:16px;"></i> Explore Free Library
        </a>
    </div>
    <?php endif; ?>
</div>
</div>
<?php include("readerFooter.php"); ?>
