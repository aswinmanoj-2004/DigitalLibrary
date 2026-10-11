<?php
require('../config/autoload.php');
if (!isset($_SESSION['reader_id'])) {
    header('location: readerLogin.php'); exit();
}
include("readerHeader.php");

$dao      = new DataAccess();
$readerId = $_SESSION['reader_id'];

$reviews = $dao->select("
    SELECT r.*, b.bname, b.CoverImage
    FROM reviews r
    LEFT JOIN books b ON r.bid = b.bid
    WHERE r.userId='$readerId'
    ORDER BY r.review_date DESC
");
?>
<style>
    .review-stars { display: flex; gap: 4px; color: #f59e0b; }
    .review-text { font-size: 13px; color: var(--text-muted); line-height: 1.5; margin-top: 6px; }
</style>

<div class="glass-card" style="padding:0; overflow:hidden;">
    <?php if ($reviews): ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Cover</th>
                <th>Book Details</th>
                <th>Rating & Review</th>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php $i = 1; foreach ($reviews as $r): ?>
            <tr>
                <td><span style="color:var(--text-muted);"><?= $i++ ?></span></td>
                <td><img src="../uploads/<?= htmlspecialchars($r['CoverImage']) ?>" style="width:40px; height:55px; object-fit:cover; border-radius:6px;" onerror="this.src='../assets/img/placeholder.png'"/></td>
                <td>
                    <div style="font-weight:600; margin-bottom:4px;"><?= htmlspecialchars($r['bname']) ?></div>
                </td>
                <td style="max-width: 300px;">
                    <div class="review-stars">
                        <?php for ($s = 1; $s <= 5; $s++): ?>
                            <i data-lucide="star" style="width:14px;height:14px; <?= $s <= $r['rating'] ? 'fill:currentColor;' : 'opacity:0.3;' ?>"></i>
                        <?php endfor; ?>
                        <span style="color:var(--text-main); font-size:12px; margin-left:6px; font-weight:600;"><?= $r['rating'] ?>.0</span>
                    </div>
                    <div class="review-text"><?= nl2br(htmlspecialchars(substr($r['review_text'], 0, 100))) ?><?= strlen($r['review_text']) > 100 ? '...' : '' ?></div>
                </td>
                <td style="color:var(--text-muted); font-size:13px;"><?= date('d M Y', strtotime($r['review_date'])) ?></td>
                <td>
                    <?= $r['status'] == 1
                        ? '<span class="badge badge-free">Visible</span>'
                        : '<span class="badge badge-paid">Hidden</span>' ?>
                </td>
                <td>
                    <a href="bookDetail.php?bid=<?= $r['bid'] ?>#review" class="btn-outline" style="padding:8px 16px; font-size:13px; display:inline-flex; align-items:center; gap:6px;">
                        <i data-lucide="edit" style="width:14px;height:14px;"></i> Edit
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div style="padding:80px 40px; text-align:center; color:var(--text-muted);">
        <i data-lucide="message-square" style="width:48px;height:48px;margin-bottom:12px;opacity:0.5;"></i>
        <div style="font-size:16px;font-weight:600;">No Reviews Yet</div>
        <div style="font-size:14px;margin-top:6px;margin-bottom:24px;">You haven't written any reviews yet.</div>
        <a href="bookList.php" class="btn">
            <i data-lucide="search" style="width:16px;height:16px;"></i> Browse Library
        </a>
    </div>
    <?php endif; ?>
</div>

<?php include("readerFooter.php"); ?>
