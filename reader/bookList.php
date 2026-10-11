<?php
require('../config/autoload.php');
if (!isset($_SESSION['reader_id'])) {
    header('location: readerLogin.php'); exit();
}
include("readerHeader.php");

$dao      = new DataAccess();
$readerId = $_SESSION['reader_id'];

// Filters
$whereParts = ["b.status=1"];
$cid   = isset($_GET['cid'])    ? intval($_GET['cid'])         : 0;
$type  = isset($_GET['type'])   ? $_GET['type']                : '';
$search= isset($_GET['search']) ? trim($_GET['search'])        : '';

if ($cid)    $whereParts[] = "b.cid='$cid'";
if ($type == 'free') $whereParts[] = "b.is_paid='free'";
if ($type == 'paid') $whereParts[] = "b.is_paid='paid'";
if ($search) $whereParts[] = "(b.bname LIKE '%$search%' OR b.AuthorName LIKE '%$search%')";

$where    = implode(" AND ", $whereParts);
$books     = $dao->query("SELECT b.*, c.cname FROM books b LEFT JOIN category c ON b.cid=c.cid WHERE $where ORDER BY b.AddedDate DESC");
$categories= $dao->query("SELECT * FROM category WHERE status=1 ORDER BY cname");

// Wishlist bids
$wishBids  = [];
$wishRows  = $dao->query("SELECT bid FROM wishlist WHERE userId='$readerId' AND status=1");
if ($wishRows) foreach ($wishRows as $w) $wishBids[] = $w['bid'];
?>
<style>
    /* ── FILTER BAR ── */
    .filter-bar { 
        display:flex; gap:16px; margin-bottom:40px; background:var(--card); 
        padding:20px; border-radius:16px; border:1px solid var(--border); 
        align-items:center; flex-wrap: wrap;
        box-shadow: 0 4px 15px rgba(9, 9, 11, 0.02);
    }
    .filter-bar .form-control { width:auto; flex:1; background:var(--input-bg); height: 46px; }
    
    /* ── BOOK GRID (Modernized) ── */
    .books-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(185px, 1fr)); gap: 22px; }
    .book-card {
        background: var(--card); border: 1px solid var(--border);
        border-radius: 16px; overflow: hidden;
        display: flex; flex-direction: column;
        transition: transform 0.28s cubic-bezier(.34,1.56,.64,1), box-shadow 0.28s, border-color 0.2s;
        box-shadow: 0 4px 15px rgba(9, 9, 11, 0.02);
    }
    .book-card:hover {
        transform: translateY(-8px);
        border-color: var(--accent);
        box-shadow: 0 12px 28px rgba(9, 9, 11, 0.05);
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
    .book-btn-primary:hover { opacity:0.88; transform:translateY(-1px); box-shadow: 0 4px 12px rgba(124, 58, 237, 0.2); }
    .book-btn-outline { background:rgba(0,0,0,0.02); color:var(--text-main); border:1px solid var(--border); }
    .book-btn-outline:hover { background:rgba(0,0,0,0.04); }
    .book-btn-danger { background:rgba(239,68,68,0.05); color:#ef4444; border:1px solid rgba(239,68,68,0.2); }
    .book-btn-danger:hover { background:rgba(239,68,68,0.1); }
</style>

<div class="filter-bar">
    <form action="" method="GET" style="display:flex; gap:16px; width:100%;">
        <input type="text" name="search" class="form-control" placeholder="Search by title or author" value="<?= htmlspecialchars($search) ?>" />
        <select name="cid" class="form-control">
            <option value="0">All Categories</option>
            <?php if($categories) foreach ($categories as $cat): ?>
                <option value="<?= $cat['cid'] ?>" <?= $cid == $cat['cid'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['cname']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="type" class="form-control">
            <option value="">All Types</option>
            <option value="free" <?= $type=='free' ? 'selected' : '' ?>>Free</option>
            <option value="paid" <?= $type=='paid' ? 'selected' : '' ?>>Premium</option>
        </select>
        <button type="submit" class="btn" style="padding:0 24px; height:46px;">
            <i data-lucide="search" style="width:16px;height:16px;"></i> Search
        </button>
        <a href="bookList.php" class="btn-outline" style="padding:0 24px; height:46px; display:inline-flex; align-items:center; gap:8px;">
            <i data-lucide="x" style="width:16px;height:16px;"></i> Reset
        </a>
    </form>
</div>

<div class="books-grid">
<?php if ($books): foreach ($books as $book): $inWishlist = in_array($book['bid'], $wishBids); ?>
    <div class="book-card">
        <div class="book-cover-wrap">
            <img src="../uploads/<?= htmlspecialchars($book['CoverImage']) ?>" class="book-cover" alt="Cover" onerror="this.src='../assets/img/placeholder.png'">
            <div class="book-badge-wrap">
                <?php if ($book['is_paid'] == 'paid'): ?>
                    <span class="badge badge-paid">₹<?= $book['price'] ?></span>
                <?php else: ?>
                    <span class="badge badge-free">Free</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="book-body">
            <div class="book-title" title="<?= htmlspecialchars($book['bname']) ?>"><?= htmlspecialchars($book['bname']) ?></div>
            <div class="book-author"><?= htmlspecialchars($book['AuthorName']) ?> &bull; <?= htmlspecialchars($book['cname']) ?></div>
            
            <div class="btn-group">
                <a href="bookDetail.php?bid=<?= $book['bid'] ?>" class="book-btn book-btn-primary">
                    <i data-lucide="eye" style="width:14px;height:14px;"></i> View
                </a>
                <?php if ($inWishlist): ?>
                    <a href="removeWishlist.php?bid=<?= $book['bid'] ?>" class="book-btn book-btn-danger" title="Remove from wishlist">
                        <i data-lucide="heart-crack" style="width:14px;height:14px;"></i>
                    </a>
                <?php else: ?>
                    <a href="addWishlist.php?bid=<?= $book['bid'] ?>" class="book-btn book-btn-outline" title="Add to wishlist">
                        <i data-lucide="heart" style="width:14px;height:14px;"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; else: ?>
    <div style="grid-column:1/-1; padding:60px; text-align:center; color:var(--text-muted); background:var(--card); border-radius:16px; border:1px dashed var(--border);">
        <i data-lucide="search-x" style="width:48px;height:48px;margin-bottom:12px;opacity:0.5;"></i>
        <div style="font-size:16px;font-weight:600;">No books found</div>
        <div style="font-size:13px;margin-top:6px;">Try adjusting your search or filters.</div>
    </div>
<?php endif; ?>
</div>

<?php include("readerFooter.php"); ?>
