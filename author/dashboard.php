<?php
require('../config/autoload.php');
if (!isset($_SESSION['authid'])) {
    header('location:login.php');
    exit();
}
$authid = $_SESSION['authid'];
$dao = new DataAccess();

// Stats: books uploaded by this author
$myBooks   = $dao->query("SELECT COUNT(*) as total FROM books WHERE status=1");
$paidBooks = $dao->query("SELECT COUNT(*) as total FROM books WHERE is_paid='paid' AND status=1");
$freeBooks = $dao->query("SELECT COUNT(*) as total FROM books WHERE is_paid='free' AND status=1");
$feedbacks = $dao->query("SELECT COUNT(*) as total FROM feedback WHERE sender_id=$authid AND sender_type='author'");

$totalBooks = $myBooks[0]['total']   ?? 0;
$totalPaid  = $paidBooks[0]['total'] ?? 0;
$totalFree  = $freeBooks[0]['total'] ?? 0;
$totalFeed  = $feedbacks[0]['total'] ?? 0;

// Recent books
$recentBooks = $dao->query("SELECT bid,bname,AuthorName,is_paid,price,AddedDate,CoverImage FROM books WHERE status=1 ORDER BY bid DESC LIMIT 5");

include("header.php");
?>

<!-- Stats Cards -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:32px;">

    <div style="background:var(--card);border:1px solid var(--border);border-radius:12px;padding:24px;">
        <div style="font-size:12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:12px;">Total Books</div>
        <div style="font-size:36px;font-weight:700;color:var(--text-main);"><?= $totalBooks ?></div>
        <div style="margin-top:10px;font-size:12px;color:#6366f1;">📚 In Library</div>
    </div>

    <div style="background:var(--card);border:1px solid var(--border);border-radius:12px;padding:24px;">
        <div style="font-size:12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:12px;">Paid Books</div>
        <div style="font-size:36px;font-weight:700;color:#f59e0b;"><?= $totalPaid ?></div>
        <div style="margin-top:10px;font-size:12px;color:#f59e0b;">💰 Premium</div>
    </div>

    <div style="background:var(--card);border:1px solid var(--border);border-radius:12px;padding:24px;">
        <div style="font-size:12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:12px;">Free Books</div>
        <div style="font-size:36px;font-weight:700;color:#10b981;"><?= $totalFree ?></div>
        <div style="margin-top:10px;font-size:12px;color:#10b981;">🎁 Open Access</div>
    </div>

    <div style="background:var(--card);border:1px solid var(--border);border-radius:12px;padding:24px;">
        <div style="font-size:12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:12px;">Feedbacks Sent</div>
        <div style="font-size:36px;font-weight:700;color:#a78bfa;"><?= $totalFeed ?></div>
        <div style="margin-top:10px;font-size:12px;color:#a78bfa;">💬 Messages</div>
    </div>

</div>

<!-- Recent Books Table -->
<div style="background:var(--card);border:1px solid var(--border);border-radius:12px;padding:28px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 style="font-size:16px;font-weight:600;">Recent Books</h2>
        <a href="Books.php" style="font-size:13px;color:var(--accent);text-decoration:none;font-weight:500;">View All →</a>
    </div>

    <div style="overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <thead>
            <tr style="border-bottom:1px solid var(--border);">
                <th style="padding:10px 12px;text-align:left;color:var(--text-muted);font-weight:500;">Cover</th>
                <th style="padding:10px 12px;text-align:left;color:var(--text-muted);font-weight:500;">Book Name</th>
                <th style="padding:10px 12px;text-align:left;color:var(--text-muted);font-weight:500;">Author</th>
                <th style="padding:10px 12px;text-align:left;color:var(--text-muted);font-weight:500;">Type</th>
                <th style="padding:10px 12px;text-align:left;color:var(--text-muted);font-weight:500;">Price</th>
                <th style="padding:10px 12px;text-align:left;color:var(--text-muted);font-weight:500;">Date</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!empty($recentBooks)): ?>
            <?php foreach ($recentBooks as $book): ?>
            <tr style="border-bottom:1px solid var(--border);">
                <td style="padding:12px;">
                    <img src="../uploads/<?= htmlspecialchars($book['CoverImage']) ?>" 
                         style="width:40px;height:55px;object-fit:cover;border-radius:4px;border:1px solid var(--border);"
                         onerror="this.style.display='none'">
                </td>
                <td style="padding:12px;font-weight:500;"><?= htmlspecialchars($book['bname']) ?></td>
                <td style="padding:12px;color:var(--text-muted);"><?= htmlspecialchars($book['AuthorName']) ?></td>
                <td style="padding:12px;">
                    <?php if ($book['is_paid'] == 'free'): ?>
                        <span style="background:rgba(16,185,129,0.1);color:#10b981;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">FREE</span>
                    <?php else: ?>
                        <span style="background:rgba(245,158,11,0.1);color:#f59e0b;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">PAID</span>
                    <?php endif; ?>
                </td>
                <td style="padding:12px;color:var(--text-muted);">
                    <?= $book['is_paid'] == 'free' ? '—' : '₹' . $book['price'] ?>
                </td>
                <td style="padding:12px;color:var(--text-muted);"><?= $book['AddedDate'] ?></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6" style="padding:30px;text-align:center;color:var(--text-muted);">No books published yet. <a href="Books.php" style="color:var(--accent);">Add your first book →</a></td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php include("footer.php"); ?>