<?php
require('../config/autoload.php');
if (!isset($_SESSION['reader_id'])) { header('location: readerLogin.php'); exit(); }
include("readerHeader.php");

$dao      = new DataAccess();
$readerId = $_SESSION['reader_id'];
$readerName = $_SESSION['reader_name'] ?? 'Reader';

$totalBooks   = $dao->query("SELECT COUNT(*) as cnt FROM books WHERE status=1");
$freeBooks    = $dao->query("SELECT COUNT(*) as cnt FROM books WHERE is_paid='free' AND status=1");
$paidBooks    = $dao->query("SELECT COUNT(*) as cnt FROM books WHERE is_paid='paid' AND status=1");
$myWishlist   = $dao->query("SELECT COUNT(*) as cnt FROM wishlist WHERE userId='$readerId' AND status=1");
$myPurchases  = $dao->query("SELECT COUNT(*) as cnt FROM purchases WHERE userId='$readerId' AND status=1");
$unreadNotifs = $dao->query("SELECT COUNT(*) as cnt FROM notifications WHERE (receiver_id='$readerId' OR receiver_id=0) AND status=1");
$recentBooks  = $dao->query("SELECT * FROM books WHERE status=1 ORDER BY AddedDate DESC LIMIT 8");
$categories   = $dao->query("SELECT * FROM category WHERE status=1 ORDER BY cname ASC");

$hour = (int)date('H');
$greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
?>
<style>
/* ── ANIMATIONS ── */
@keyframes fadeUp   { from { opacity:0; transform:translateY(24px); } to { opacity:1; transform:translateY(0); } }
@keyframes countUp  { from { opacity:0; transform:scale(0.7); } to { opacity:1; transform:scale(1); } }
@keyframes shimmer  { 0%,100%{background-position:200% center;} 50%{background-position:-200% center;} }
@keyframes pulse    { 0%,100%{transform:scale(1);} 50%{transform:scale(1.05);} }
@keyframes slideIn  { from{opacity:0;transform:translateX(-20px);} to{opacity:1;transform:translateX(0);} }

.fade-up  { opacity:0; animation:fadeUp 0.6s ease forwards; }
.delay-1  { animation-delay:0.1s; }
.delay-2  { animation-delay:0.2s; }
.delay-3  { animation-delay:0.3s; }
.delay-4  { animation-delay:0.4s; }
.delay-5  { animation-delay:0.5s; }

/* ── HERO GREETING ── */
.hero-banner {
    background: linear-gradient(135deg, #e0e7ff 0%, #f1f5f9 100%);
    border: 1px solid rgba(124, 58, 237, 0.15);
    border-radius: 20px;
    padding: 36px 40px;
    margin-bottom: 32px;
    position: relative;
    overflow: hidden;
    animation: fadeUp 0.5s ease both;
}
.hero-banner::before {
    content: '';
    position: absolute; top: -60px; right: -60px;
    width: 280px; height: 280px;
    background: radial-gradient(circle, rgba(124, 58, 237, 0.1) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}
.hero-banner::after {
    content: '';
    position: absolute; bottom: -40px; left: 20%;
    width: 180px; height: 180px;
    background: radial-gradient(circle, rgba(124, 58, 237, 0.05) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}
.hero-greeting {
    font-size: 13px; font-weight: 600; color: var(--accent);
    text-transform: uppercase; letter-spacing: 2px; margin-bottom: 8px;
    display: flex; align-items: center; gap: 8px;
}
.hero-name { font-size: 32px; font-weight: 800; margin-bottom: 6px; letter-spacing:-1px; color: var(--text-main); }
.hero-sub  { font-size: 14px; color: var(--text-muted); }
.hero-actions { display:flex; gap:12px; margin-top:24px; flex-wrap:wrap; }
.hero-btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 22px; border-radius: 10px; font-size: 13px;
    font-weight: 600; text-decoration: none; transition: all 0.2s;
}
.hero-btn-primary {
    background: var(--accent); color: #fff;
    box-shadow: 0 4px 12px rgba(124, 58, 237, 0.25);
}
.hero-btn-primary:hover { opacity:0.9; transform:translateY(-1px); color:#fff; }
.hero-btn-ghost {
    background: var(--card); color: var(--text-main);
    border: 1px solid var(--border);
}
.hero-btn-ghost:hover { background: rgba(0,0,0,0.02); }

/* ── NOTIF BANNER ── */
.notif-bar {
    display: flex; align-items: center; gap: 14px;
    padding: 14px 20px; border-radius: 12px; margin-bottom: 28px;
    background: rgba(56,189,248,0.05);
    border: 1px solid rgba(56,189,248,0.2);
    animation: slideIn 0.5s ease both;
}
.notif-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: #0284c7; flex-shrink: 0;
    animation: pulse 1.5s ease-in-out infinite;
}

/* ── STATS GRID ── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 18px;
    margin-bottom: 40px;
}
.stat-card {
    background: var(--card); border: 1px solid var(--border);
    border-radius: 16px; padding: 22px 24px;
    display: flex; align-items: center; gap: 18px;
    text-decoration: none; transition: all 0.25s cubic-bezier(.34,1.56,.64,1);
    position: relative; overflow: hidden;
    box-shadow: 0 4px 15px rgba(9, 9, 11, 0.02);
}
.stat-card::after {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(135deg, transparent 60%, rgba(0,0,0,0.005));
    pointer-events: none;
}
.stat-card:hover {
    transform: translateY(-5px);
    border-color: rgba(124, 58, 237, 0.3);
    box-shadow: 0 10px 25px rgba(9, 9, 11, 0.05);
}
.stat-icon {
    width: 52px; height: 52px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.stat-val {
    font-size: 30px; font-weight: 800; line-height: 1;
    margin-bottom: 4px;
    animation: countUp 0.5s cubic-bezier(.34,1.56,.64,1) both;
}
.stat-lbl { font-size: 13px; color: var(--text-muted); font-weight: 500; }

/* ── SECTION HEADER ── */
.sec-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 20px;
}
.sec-title {
    font-size: 20px; font-weight: 700; letter-spacing: -0.3px;
    display: flex; align-items: center; gap: 10px;
}
.sec-link {
    font-size: 13px; color: var(--accent); text-decoration: none;
    font-weight: 600; display: flex; align-items: center; gap: 5px;
    transition: gap 0.2s;
}
.sec-link:hover { gap: 8px; }

/* ── CATEGORY GRID ── */
.cat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(155px, 1fr));
    gap: 16px; margin-bottom: 44px;
}
.cat-card {
    position: relative; border-radius: 16px; overflow: hidden;
    aspect-ratio: 1/1; text-decoration: none; display: block;
    border: 1px solid var(--border);
    transition: transform 0.35s cubic-bezier(.34,1.56,.64,1), box-shadow 0.35s, border-color 0.25s;
    box-shadow: 0 4px 15px rgba(9, 9, 11, 0.02);
}
.cat-card:hover {
    transform: translateY(-8px) scale(1.03);
    border-color: var(--accent);
    box-shadow: 0 16px 32px rgba(9, 9, 11, 0.08), 0 0 0 1px rgba(124, 58, 237, 0.1);
}
.cat-card img {
    width:100%; height:100%; object-fit:cover;
    transition: transform 0.5s ease;
}
.cat-card:hover img { transform: scale(1.12); }
.cat-overlay {
    position:absolute; inset:0;
    background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.1) 55%, transparent);
    display:flex; flex-direction:column; align-items:center; justify-content:flex-end;
    padding:14px 10px;
}
.cat-name { font-size:14px; font-weight:700; color:#fff; text-align:center; }
.cat-explore {
    margin-top:6px; font-size:10px; font-weight:700;
    letter-spacing:1px; text-transform:uppercase;
    background: rgba(124, 58, 237, 0.15); color: #c084fc;
    border:1px solid rgba(124, 58, 237, 0.3); padding:2px 10px; border-radius:20px;
    opacity:0; transform:translateY(6px); transition: all 0.25s;
}
.cat-card:hover .cat-explore { opacity:1; transform:translateY(0); }

/* ── BOOK GRID ── */
.books-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(185px, 1fr));
    gap: 22px;
}
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
    width:100%; height:240px; object-fit:cover; display:block;
    transition: transform 0.4s ease;
}
.book-card:hover .book-cover { transform: scale(1.06); }
.book-badge-wrap {
    position:absolute; top:10px; right:10px;
}
.book-body { padding:14px; display:flex; flex-direction:column; flex:1; }
.book-title {
    font-size:14px; font-weight:700; margin-bottom:4px;
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
    color:var(--text-main);
}
.book-author { font-size:12px; color:var(--text-muted); margin-bottom:14px; }
.book-btn {
    display:flex; align-items:center; justify-content:center; gap:6px;
    padding:9px; background:var(--accent); color:#fff; border-radius:8px;
    text-decoration:none; font-size:13px; font-weight:600;
    margin-top:auto; transition:opacity 0.2s, transform 0.2s;
}
.book-btn:hover { opacity:0.88; transform:translateY(-1px); color:#fff; box-shadow: 0 4px 12px rgba(124, 58, 237, 0.2); }

/* ── QUICK LINKS ROW ── */
.quick-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 14px; margin-bottom: 40px;
}
.quick-card {
    background: var(--card); border: 1px solid var(--border);
    border-radius: 14px; padding: 20px 16px; text-align: center;
    text-decoration: none; transition: all 0.22s;
    display: flex; flex-direction: column; align-items: center; gap: 10px;
    box-shadow: 0 4px 15px rgba(9, 9, 11, 0.02);
}
.quick-card:hover { border-color: var(--accent); background: var(--accent-glow); transform:translateY(-3px); box-shadow: 0 8px 20px rgba(124, 58, 237, 0.05); }
.quick-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; }
.quick-label { font-size:13px; font-weight:600; color:var(--text-main); }
</style>

<?php $unread = $unreadNotifs[0]['cnt'] ?? 0; ?>
<?php if ($unread > 0): ?>
<div class="notif-bar fade-up">
    <div class="notif-dot"></div>
    <div style="flex:1; font-size:14px;">
        You have <strong style="color:#38bdf8;"><?= $unread ?></strong> unread notification<?= $unread>1?'s':''; ?>.
    </div>
    <a href="notifications.php" style="color:#38bdf8; font-size:13px; font-weight:700; text-decoration:none; white-space:nowrap; display:flex; align-items:center; gap:5px;">
        <i data-lucide="bell" style="width:14px;height:14px;"></i> View
    </a>
</div>
<?php endif; ?>

<!-- HERO -->
<div class="hero-banner">
    <div class="hero-greeting">
        <i data-lucide="sun" style="width:15px;height:15px;"></i> <?= $greeting ?>
    </div>
    <div class="hero-name">Welcome back, <?= htmlspecialchars($readerName) ?> 👋</div>
    <div class="hero-sub">Your personal library is waiting. Discover your next great read.</div>
    <div class="hero-actions">
        <a href="bookList.php" class="hero-btn hero-btn-primary">
            <i data-lucide="book-open" style="width:15px;height:15px;"></i> Browse Library
        </a>
        <a href="myPurchases.php" class="hero-btn hero-btn-ghost">
            <i data-lucide="shopping-bag" style="width:15px;height:15px;"></i> My Purchases
        </a>
        <a href="wishlist.php" class="hero-btn hero-btn-ghost">
            <i data-lucide="heart" style="width:15px;height:15px;"></i> Wishlist
        </a>
    </div>
</div>

<!-- STATS -->
<div class="stats-grid">
    <a href="bookList.php" class="stat-card fade-up delay-1">
        <div class="stat-icon" style="background:rgba(59,130,246,0.12); color:#3b82f6;">
            <i data-lucide="library" style="width:26px;height:26px;"></i>
        </div>
        <div>
            <div class="stat-val" style="color:#3b82f6;"><?= $totalBooks[0]['cnt'] ?? 0 ?></div>
            <div class="stat-lbl">Total Books</div>
        </div>
    </a>
    <a href="bookList.php?type=free" class="stat-card fade-up delay-2">
        <div class="stat-icon" style="background:rgba(16,185,129,0.12); color:#10b981;">
            <i data-lucide="unlock" style="width:26px;height:26px;"></i>
        </div>
        <div>
            <div class="stat-val" style="color:#10b981;"><?= $freeBooks[0]['cnt'] ?? 0 ?></div>
            <div class="stat-lbl">Free Books</div>
        </div>
    </a>
    <a href="bookList.php?type=paid" class="stat-card fade-up delay-3">
        <div class="stat-icon" style="background:rgba(245,158,11,0.12); color:#f59e0b;">
            <i data-lucide="lock" style="width:26px;height:26px;"></i>
        </div>
        <div>
            <div class="stat-val" style="color:#f59e0b;"><?= $paidBooks[0]['cnt'] ?? 0 ?></div>
            <div class="stat-lbl">Premium Books</div>
        </div>
    </a>
    <a href="wishlist.php" class="stat-card fade-up delay-4">
        <div class="stat-icon" style="background:rgba(239,68,68,0.12); color:#ef4444;">
            <i data-lucide="heart" style="width:26px;height:26px;"></i>
        </div>
        <div>
            <div class="stat-val" style="color:#ef4444;"><?= $myWishlist[0]['cnt'] ?? 0 ?></div>
            <div class="stat-lbl">My Wishlist</div>
        </div>
    </a>
    <a href="myPurchases.php" class="stat-card fade-up delay-5">
        <div class="stat-icon" style="background:rgba(168,85,247,0.12); color:#a855f7;">
            <i data-lucide="shopping-bag" style="width:26px;height:26px;"></i>
        </div>
        <div>
            <div class="stat-val" style="color:#a855f7;"><?= $myPurchases[0]['cnt'] ?? 0 ?></div>
            <div class="stat-lbl">Purchased</div>
        </div>
    </a>
</div>

<!-- QUICK LINKS -->
<div class="sec-header fade-up delay-2">
    <div class="sec-title">
        <i data-lucide="zap" style="width:20px;height:20px;stroke:var(--accent);"></i> Quick Access
    </div>
</div>
<div class="quick-grid fade-up delay-3" style="margin-bottom:40px;">
    <a href="bookList.php" class="quick-card">
        <div class="quick-icon" style="background:rgba(59,130,246,0.12); color:#3b82f6;"><i data-lucide="book-open" style="width:22px;height:22px;"></i></div>
        <div class="quick-label">Browse Books</div>
    </a>
    <a href="wishlist.php" class="quick-card">
        <div class="quick-icon" style="background:rgba(239,68,68,0.12); color:#ef4444;"><i data-lucide="heart" style="width:22px;height:22px;"></i></div>
        <div class="quick-label">Wishlist</div>
    </a>
    <a href="myPurchases.php" class="quick-card">
        <div class="quick-icon" style="background:rgba(168,85,247,0.12); color:#a855f7;"><i data-lucide="shopping-bag" style="width:22px;height:22px;"></i></div>
        <div class="quick-label">Purchases</div>
    </a>
    <a href="myDownloads.php" class="quick-card">
        <div class="quick-icon" style="background:rgba(16,185,129,0.12); color:#10b981;"><i data-lucide="download" style="width:22px;height:22px;"></i></div>
        <div class="quick-label">Downloads</div>
    </a>
    <a href="complaints.php" class="quick-card">
        <div class="quick-icon" style="background:rgba(245,158,11,0.12); color:#f59e0b;"><i data-lucide="flag" style="width:22px;height:22px;"></i></div>
        <div class="quick-label">Complaints</div>
    </a>
    <a href="notifications.php" class="quick-card">
        <div class="quick-icon" style="background:rgba(56,189,248,0.12); color:#38bdf8;">
            <i data-lucide="bell" style="width:22px;height:22px;"></i>
        </div>
        <div class="quick-label" style="position:relative;">
            Notifications
            <?php if ($unread > 0): ?>
            <span style="position:absolute;top:-28px;right:-8px;background:#ef4444;color:#fff;font-size:10px;font-weight:700;padding:2px 6px;border-radius:20px;"><?= $unread ?></span>
            <?php endif; ?>
        </div>
    </a>
</div>

<!-- CATEGORIES -->
<?php if ($categories && count($categories) > 0): ?>
<div class="sec-header fade-up">
    <div class="sec-title">
        <i data-lucide="layout-grid" style="width:20px;height:20px;stroke:var(--accent);"></i>
        Browse by Category
    </div>
    <a href="bookList.php" class="sec-link">
        All Books <i data-lucide="arrow-right" style="width:14px;height:14px;"></i>
    </a>
</div>
<div class="cat-grid fade-up delay-1">
    <?php foreach ($categories as $cat): ?>
    <a href="bookList.php?cid=<?= $cat['cid'] ?>" class="cat-card">
        <img src="../uploads/<?= htmlspecialchars($cat['cimage']) ?>" alt="<?= htmlspecialchars($cat['cname']) ?>" onerror="this.src='assets/img/placeholder.png'">
        <div class="cat-overlay">
            <div class="cat-name"><?= htmlspecialchars($cat['cname']) ?></div>
            <div class="cat-explore">Explore</div>
        </div>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- RECENT BOOKS -->
<div class="sec-header fade-up">
    <div class="sec-title">
        <i data-lucide="sparkles" style="width:20px;height:20px;stroke:var(--accent);"></i>
        New Arrivals
    </div>
    <a href="bookList.php" class="sec-link">
        View all <i data-lucide="arrow-right" style="width:14px;height:14px;"></i>
    </a>
</div>

<div class="books-grid fade-up delay-2" id="booksGrid">
    <?php if ($recentBooks && count($recentBooks) > 0): foreach ($recentBooks as $b): ?>
    <div class="book-card">
        <div class="book-cover-wrap">
            <img src="../uploads/<?= htmlspecialchars($b['CoverImage']) ?>" class="book-cover" alt="Cover" onerror="this.src='assets/img/placeholder.png'">
            <div class="book-badge-wrap">
                <?php if ($b['is_paid']=='paid'): ?>
                <span class="badge badge-paid">₹<?= $b['price'] ?></span>
                <?php else: ?>
                <span class="badge badge-free">Free</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="book-body">
            <div class="book-title" title="<?= htmlspecialchars($b['bname']) ?>"><?= htmlspecialchars($b['bname']) ?></div>
            <div class="book-author"><?= htmlspecialchars($b['AuthorName']) ?></div>
            <a href="bookDetail.php?bid=<?= $b['bid'] ?>" class="book-btn">
                <i data-lucide="eye" style="width:14px;height:14px;"></i> View Details
            </a>
        </div>
    </div>
    <?php endforeach; else: ?>
    <div style="grid-column:1/-1;padding:60px;text-align:center;color:var(--text-muted);background:var(--card);border-radius:16px;border:1px dashed var(--border);">
        <div style="font-size:36px;margin-bottom:12px;">📚</div>
        No books in the library yet.
    </div>
    <?php endif; ?>
</div>

<script>
// Scroll-triggered reveal
const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            e.target.style.opacity = '1';
            e.target.style.transform = 'translateY(0)';
            observer.unobserve(e.target);
        }
    });
}, { threshold: 0.1 });

document.querySelectorAll('.book-card').forEach((el, i) => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(30px)';
    el.style.transition = `opacity 0.5s ease ${i * 0.07}s, transform 0.5s ease ${i * 0.07}s, border-color 0.2s, box-shadow 0.28s`;
    observer.observe(el);
});

document.querySelectorAll('.cat-card').forEach((el, i) => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = `opacity 0.4s ease ${i * 0.06}s, transform 0.4s ease ${i * 0.06}s, border-color 0.2s, box-shadow 0.35s`;
    observer.observe(el);
});

document.querySelectorAll('.quick-card').forEach((el, i) => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(16px)';
    el.style.transition = `opacity 0.4s ease ${i * 0.05}s, transform 0.4s ease ${i * 0.05}s, all 0.22s`;
    observer.observe(el);
});
</script>

<?php include("readerFooter.php"); ?>
