<?php
require('../config/autoload.php');
if (!isset($_SESSION['reader_id'])) {
    header('location: readerLogin.php'); exit();
}
include("readerHeader.php");

$dao      = new DataAccess();
$readerId = $_SESSION['reader_id'];

$wishlist = $dao->query("
    SELECT w.wid, w.AddedDate, b.bid, b.bname, b.AuthorName, b.CoverImage, b.is_paid, b.price, c.cname
    FROM wishlist w
    LEFT JOIN books b ON w.bid = b.bid
    LEFT JOIN category c ON b.cid = c.cid
    WHERE w.userId='$readerId' AND w.status=1 AND b.status=1
    ORDER BY w.AddedDate DESC
");
?>
<style>
    /* ── BOOK GRID (Modernized) ── */
    .books-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(185px, 1fr)); gap: 22px; }
    .book-card {
        background: var(--card); border: 1px solid var(--border);
        border-radius: 16px; overflow: hidden;
        display: flex; flex-direction: column;
        transition: transform 0.28s cubic-bezier(.34,1.56,.64,1), box-shadow 0.28s, border-color 0.2s;
    }
    .book-card:hover {
        transform: translateY(-8px);
        border-color: var(--accent);
        box-shadow: 0 16px 36px rgba(0,0,0,0.5);
    }
    .book-cover-wrap { position: relative; overflow: hidden; }
    .book-cover {
        width:100%; height:260px; object-fit:cover; display:block;
        transition: transform 0.4s ease;
    }
    .book-card:hover .book-cover { transform: scale(1.06); }
    .book-badge-wrap { position:absolute; top:10px; right:10px; }
    .book-body { padding:14px; display:flex; flex-direction:column; flex:1; }
    .book-title {
        font-size:14px; font-weight:700; margin-bottom:4px;
        white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
        color:var(--text-main);
    }
    .book-author { font-size:12px; color:var(--text-muted); margin-bottom:14px; }
    
    .btn-group { display: flex; gap: 8px; margin-top: auto; }
    .book-btn {
        display:flex; align-items:center; justify-content:center; gap:6px;
        padding:9px; border-radius:8px; flex: 1;
        text-decoration:none; font-size:12px; font-weight:600;
        transition:opacity 0.2s, transform 0.2s;
    }
    .book-btn-primary { background:var(--accent); color:#fff; }
    .book-btn-primary:hover { opacity:0.88; transform:translateY(-1px); }
    .book-btn-danger { background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.3); }
    .book-btn-danger:hover { background:rgba(239,68,68,0.2); }
    .book-btn-report { 
        display:flex; align-items:center; justify-content:center; gap:6px; margin-top:8px; 
        padding:8px; font-size:12px; text-align:center; border:1px dashed rgba(239,68,68,0.35); 
        color:#ef4444; border-radius:8px; text-decoration:none; transition:background 0.2s;
    }
    .book-btn-report:hover { background:rgba(239,68,68,0.08); }
</style>

<div class="books-grid">
<?php if ($wishlist): foreach ($wishlist as $item): ?>
    <div class="book-card">
        <div class="book-cover-wrap">
            <img src="../uploads/<?= htmlspecialchars($item['CoverImage']) ?>" class="book-cover" alt="Cover" onerror="this.src='../assets/img/placeholder.png'">
            <div class="book-badge-wrap">
                <?php if ($item['is_paid'] == 'paid'): ?>
                    <span class="badge badge-paid">₹<?= $item['price'] ?></span>
                <?php else: ?>
                    <span class="badge badge-free">Free</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="book-body">
            <div class="book-title" title="<?= htmlspecialchars($item['bname']) ?>"><?= htmlspecialchars($item['bname']) ?></div>
            <div class="book-author"><?= htmlspecialchars($item['AuthorName']) ?> &bull; <?= htmlspecialchars($item['cname']) ?></div>
            
            <div class="btn-group">
                <a href="bookDetail.php?bid=<?= $item['bid'] ?>" class="book-btn book-btn-primary" style="flex:2;">
                    <i data-lucide="eye" style="width:14px;height:14px;"></i> Details
                </a>
                <a href="removeWishlist.php?bid=<?= $item['bid'] ?>&from=wishlist" class="book-btn book-btn-danger" title="Remove from wishlist" onclick="return confirm('Remove from wishlist?')">
                    <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                </a>
            </div>
            
            <a href="complaints.php?bid=<?= $item['bid'] ?>&type=Book+Content" class="book-btn-report" title="Report an issue with this book">
                <i data-lucide="flag" style="width:13px;height:13px;"></i> Report Book
            </a>
        </div>
    </div>
<?php endforeach; else: ?>
    <div style="grid-column:1/-1; padding:60px; text-align:center; color:var(--text-muted); background:var(--card); border-radius:16px; border:1px dashed var(--border);">
        <i data-lucide="heart" style="width:48px;height:48px;margin-bottom:12px;opacity:0.5;stroke:var(--text-muted);"></i>
        <div style="font-size:16px;font-weight:600;">Your wishlist is empty</div>
        <div style="font-size:13px;margin-top:6px;">
            <a href="bookList.php" style="color:var(--accent);text-decoration:none;">Browse books</a> to add some!
        </div>
    </div>
<?php endif; ?>
</div>

<?php include("readerFooter.php"); ?>
