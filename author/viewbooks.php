<?php
require('../config/autoload.php');
if (!isset($_SESSION['authid'])) { header('location:login.php'); exit(); }

$authid = $_SESSION['authid'];
$authorName = $_SESSION['authname'];
$dao = new DataAccess();

include("header.php");
?>

<style>
    .book-card {
        background: var(--card); border: 1px solid var(--border);
        border-radius: 12px; overflow: hidden; display: flex;
        flex-direction: column; transition: transform 0.2s;
    }
    .book-card:hover { transform: translateY(-3px); border-color: var(--accent); }
    .book-cover { width: 100%; height: 200px; object-fit: cover; border-bottom: 1px solid var(--border); }
    .book-info { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; }
    .book-title { font-size: 16px; font-weight: 600; margin-bottom: 8px; color: var(--text-main); }
    .book-cat { font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 15px; }
    .book-meta { display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.05); }
    
    .rating-box { display: flex; align-items: center; gap: 5px; font-size: 13px; font-weight: 600; color: #f59e0b; }
    .stat-badge { background: rgba(255,255,255,0.03); padding: 4px 10px; border-radius: 6px; font-size: 12px; color: var(--text-muted); }
    
    .review-item { padding: 15px 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
    .review-item:last-child { border-bottom: none; }
    .r-header { display: flex; justify-content: space-between; margin-bottom: 6px; }
    .r-user { font-weight: 500; font-size: 13px; }
    .r-date { font-size: 11px; color: var(--text-muted); }
    .r-text { font-size: 13px; color: var(--text-muted); line-height: 1.5; }
</style>

<div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:24px;">
    <div>
        <h2 style="font-size:18px; font-weight:600;">My Published Books & Reviews</h2>
        <p style="color:var(--text-muted); font-size:13px; margin-top:4px;">Manage your catalog and see what readers are saying.</p>
    </div>
    <a href="Books.php" class="btn" style="display:flex; align-items:center; gap:8px;">
        <i data-lucide="plus" style="width:16px;height:16px;"></i> Publish New Book
    </a>
</div>

<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:24px;">
    <?php
    $safeAuthorName = addslashes($authorName);
    // Get books by this author
    $books = $dao->query("SELECT b.bid, b.bname, b.CoverImage, b.is_paid, b.price, c.cname 
                          FROM books b 
                          LEFT JOIN category c ON b.cid = c.cid 
                          WHERE b.AuthorName = '$safeAuthorName' AND b.status = 1 
                          ORDER BY b.bid DESC");

    if (!empty($books)):
        foreach ($books as $b):
            $bid = $b['bid'];
            // Get review stats
            $revStats = $dao->query("SELECT COUNT(*) as cnt, AVG(rating) as avg FROM reviews WHERE bid=$bid AND status=1");
            $rCnt = $revStats[0]['cnt'] ?? 0;
            $rAvg = round($revStats[0]['avg'] ?? 0, 1);
            
            // Get recent reviews
            $reviews = $dao->query("SELECT rv.rating, rv.review_text, rv.review_date, r.username 
                                    FROM reviews rv 
                                    JOIN readerregistration r ON rv.userId = r.userId 
                                    WHERE rv.bid=$bid AND rv.status=1 
                                    ORDER BY rv.review_date DESC LIMIT 3");
    ?>
    <div class="book-card">
        <img src="../uploads/<?= htmlspecialchars($b['CoverImage']) ?>" class="book-cover" onerror="this.src='../uploads/placeholder.png'">
        <div class="book-info">
            <div class="book-title"><?= htmlspecialchars($b['bname']) ?></div>
            <div class="book-cat"><?= htmlspecialchars($b['cname'] ?? 'Uncategorized') ?></div>
            
            <div style="margin-bottom:15px;">
                <?php if ($b['is_paid'] == 'free'): ?>
                    <span class="badge badge-free">Free</span>
                <?php else: ?>
                    <span class="badge badge-paid">₹<?= $b['price'] ?></span>
                <?php endif; ?>
            </div>

            <!-- Reviews Preview -->
            <?php if (!empty($reviews)): ?>
                <div style="background:rgba(0,0,0,0.2); padding:10px 15px; border-radius:8px; margin-bottom:15px;">
                    <div style="font-size:11px; text-transform:uppercase; color:var(--text-muted); margin-bottom:8px; font-weight:600;">Recent Reviews</div>
                    <?php foreach ($reviews as $rv): ?>
                        <div class="review-item">
                            <div class="r-header">
                                <span class="r-user"><?= htmlspecialchars($rv['username']) ?> <span style="color:#f59e0b;">(<?= $rv['rating'] ?>★)</span></span>
                                <span class="r-date"><?= date('M d, Y', strtotime($rv['review_date'])) ?></span>
                            </div>
                            <div class="r-text">"<?= htmlspecialchars($rv['review_text']) ?>"</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="font-size:12px; color:var(--text-muted); font-style:italic; margin-bottom:15px;">No reviews yet.</div>
            <?php endif; ?>

            <div class="book-meta">
                <div class="stat-badge">Reviews: <?= $rCnt ?></div>
                <div class="rating-box">
                    <span><?= $rAvg > 0 ? $rAvg : '—' ?></span> ★
                </div>
            </div>

            <!-- Edit / Delete Actions -->
            <div style="display:flex; gap:8px; margin-top:16px; padding-top:14px; border-top:1px solid rgba(255,255,255,0.05);">
                <a href="editBook.php?bid=<?= $bid ?>" class="btn" style="flex:1; justify-content:center; gap:6px; padding:9px; font-size:13px;">
                    <i data-lucide="pencil" style="width:14px;height:14px;"></i> Edit
                </a>
                <a href="editBook.php?bid=<?= $bid ?>&delete=1"
                   class="btn" style="background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.25); padding:9px 14px; font-size:13px; display:flex; align-items:center; gap:6px;"
                   onclick="return confirm('Delete this book? This cannot be undone.')">
                    <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; else: ?>
        <div style="grid-column:1/-1; padding:40px; text-align:center; background:var(--card); border-radius:12px; border:1px solid var(--border); color:var(--text-muted);">
            You haven't published any books yet.
        </div>
    <?php endif; ?>
</div>

<?php include("footer.php"); ?>
