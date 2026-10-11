<?php
require('../config/autoload.php');
if (!isset($_SESSION['reader_id'])) {
    header('location: readerLogin.php'); exit();
}

$dao      = new DataAccess();
$readerId = $_SESSION['reader_id'];
$bid      = isset($_GET['bid']) ? intval($_GET['bid']) : 0;

if (!$bid) { header('location: bookList.php'); exit(); }

$books = $dao->query("SELECT b.*, c.cname FROM books b LEFT JOIN category c ON b.cid=c.cid WHERE b.bid='$bid' AND b.status=1");
if (!$books) { header('location: bookList.php'); exit(); }
$book = $books[0];

// Check wishlist
$inWishlist = $dao->query("SELECT * FROM wishlist WHERE userId='$readerId' AND bid='$bid' AND status=1");

// Check purchase (for paid books)
$purchased = $dao->query("SELECT * FROM purchases WHERE userId='$readerId' AND bid='$bid' AND payment_status='completed' AND status=1");

// Check download (for free books)
$downloaded = $dao->query("SELECT * FROM downloads WHERE userId='$readerId' AND bid='$bid' AND status=1");

// Reviews for this book
$reviews = $dao->query("SELECT r.*, rr.username FROM reviews r LEFT JOIN readerregistration rr ON r.userId=rr.userId WHERE r.bid='$bid' AND r.status=1 ORDER BY r.review_date DESC");

// Average rating
$avgRating = $dao->query("SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM reviews WHERE bid='$bid' AND status=1");
$avgR = ($avgRating && $avgRating[0]['avg_rating'] !== null) ? round((float)$avgRating[0]['avg_rating'], 1) : 0;
$totalR = $avgRating ? (int)$avgRating[0]['total'] : 0;

// My existing review
$myReview = $dao->query("SELECT * FROM reviews WHERE userId='$readerId' AND bid='$bid'");

$msg = "";

// Handle review submission
if (isset($_POST['btn_review'])) {
    $rating      = intval($_POST['rating']);
    $review_text = trim($_POST['review_text']);
    if ($rating >= 1 && $rating <= 5 && $review_text) {
        if ($myReview) {
            $dao->update(['rating' => $rating, 'review_text' => $review_text], "reviews", "reviewid='" . $myReview[0]['reviewid'] . "'");
        } else {
            $data = [
                'bid'         => $bid,
                'userId'      => $readerId,
                'rating'      => $rating,
                'review_text' => $review_text,
                'review_date' => date('Y-m-d H:i:s'),
                'status'      => 1
            ];
            $dao->insert($data, "reviews");
        }
        header("location: bookDetail.php?bid=$bid");
        exit();
    }
}

// Handle feedback submission for this book
if (isset($_POST['btn_feedback'])) {
    $fb_subject = trim($_POST['fb_subject']);
    $fb_message = trim($_POST['fb_message']);
    if (strlen($fb_subject) >= 5 && strlen($fb_message) >= 10) {
        $fb_data = [
            'sender_id'     => $readerId,
            'sender_type'   => 'reader',
            'subject'       => $fb_subject,
            'message'       => $fb_message,
            'feedback_date' => date('Y-m-d H:i:s'),
            'status'        => 1,
        ];
        if ($dao->insert($fb_data, "feedback")) {
            echo "<script>alert('Feedback submitted! Thank you.');</script>";
            header("location: bookDetail.php?bid=$bid#feedback");
            exit();
        }
    } else {
        $msg = "Feedback subject (min 5 chars) and message (min 10 chars) are required.";
    }
}

include("readerHeader.php");
?>
<style>
    .detail-grid { display: grid; grid-template-columns: 300px 1fr; gap: 40px; margin-bottom: 60px; }
    .cover-container { background: var(--card); padding: 20px; border-radius: 16px; border: 1px solid var(--border); text-align: center; box-shadow: 0 4px 15px rgba(9, 9, 11, 0.02); }
    .cover-img { width: 100%; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 8px 24px rgba(9, 9, 11, 0.12); }
    .book-meta { display: flex; gap: 20px; color: var(--text-muted); font-size: 14px; margin-bottom: 24px; align-items: center;}
    .book-meta span { display: flex; align-items: center; gap: 6px; }
    .rating-stars { color: #f59e0b; letter-spacing: 2px; }
    
    .desc-box { background: rgba(0, 0, 0, 0.015); padding: 24px; border-radius: 12px; border: 1px solid var(--border); line-height: 1.8; margin-bottom: 30px; font-size: 15px; }
    
    .action-panel { display: flex; align-items: center; gap: 20px; padding: 24px; background: rgba(16,185,129,0.05); border: 1px solid rgba(16,185,129,0.2); border-radius: 12px; }
    .action-panel.paid { background: rgba(245,158,11,0.05); border-color: rgba(245,158,11,0.2); }
    
    .review-card { background: var(--card); padding: 24px; border-radius: 12px; border: 1px solid var(--border); margin-bottom: 20px; box-shadow: 0 4px 15px rgba(9, 9, 11, 0.02); }
    .review-header { display: flex; justify-content: space-between; margin-bottom: 12px; }
</style>

<a href="bookList.php" class="btn-outline" style="display:inline-flex; align-items:center; gap:8px; margin-bottom:30px;">
    <i data-lucide="arrow-left" style="width:16px;height:16px;"></i> Back to Library
</a>

<div class="detail-grid">
    <div class="cover-container">
        <img src="../uploads/<?= htmlspecialchars($book['CoverImage'] ?? '') ?>" class="cover-img" alt="Cover" onerror="this.src='../assets/img/placeholder.png'">
        
        <?php if ($inWishlist): ?>
            <a href="removeWishlist.php?bid=<?= $bid ?>&from=detail" class="btn btn-danger" style="display:flex; align-items:center; justify-content:center; gap:8px; width:100%; padding:12px; margin-bottom:12px;">
                <i data-lucide="heart-crack" style="width:16px;height:16px;"></i> Remove Wishlist
            </a>
        <?php else: ?>
            <a href="addWishlist.php?bid=<?= $bid ?>&from=detail" class="btn-outline" style="display:flex; align-items:center; justify-content:center; gap:8px; width:100%; padding:12px; margin-bottom:12px;">
                <i data-lucide="heart" style="width:16px;height:16px;"></i> Add to Wishlist
            </a>
        <?php endif; ?>
    </div>

    <div>
        <h1 style="font-size:40px; font-weight:800; margin-bottom:16px; letter-spacing:-1px;"><?= htmlspecialchars($book['bname'] ?? '') ?></h1>
        
        <div class="book-meta">
            <span><i data-lucide="user" style="width:16px;height:16px;stroke:var(--text-muted);"></i> <strong><?= htmlspecialchars($book['AuthorName'] ?? '') ?></strong></span>
            <span><i data-lucide="tag" style="width:16px;height:16px;stroke:var(--text-muted);"></i> <?= htmlspecialchars($book['cname'] ?? '') ?></span>
            <span><i data-lucide="calendar" style="width:16px;height:16px;stroke:var(--text-muted);"></i> <?= date('M Y', strtotime($book['AddedDate'])) ?></span>
        </div>

        <div style="margin-bottom:30px; font-size:18px; display:flex; align-items:center;">
            <div class="rating-stars" style="display:flex; gap:4px;">
                <?php for($s=1;$s<=5;$s++): ?>
                    <i data-lucide="star" style="width:20px;height:20px; <?= $s <= $avgR ? 'fill:currentColor;' : 'opacity:0.3;' ?>"></i>
                <?php endfor; ?>
            </div>
            <span style="color:var(--text-muted); font-size:14px; margin-left:10px;"><?= $avgR ?>/5 (<?= $totalR ?> reviews)</span>
        </div>

        <div class="desc-box">
            <h4 style="margin-top:0; margin-bottom:12px; color:var(--text-main);">About this book</h4>
            <?= nl2br(htmlspecialchars($book['description'] ?? '')) ?>
        </div>

        <?php if ($book['is_paid'] == 'free'): ?>
            <div class="action-panel">
                <div>
                    <h3 style="margin:0 0 4px; color:var(--accent);">Free to Read</h3>
                    <p style="margin:0; font-size:13px; color:var(--text-muted);">This book is available for free download.</p>
                </div>
                <div style="margin-left:auto;">
                    <?php if ($downloaded): ?>
                        <a href="../uploadpdf/<?= htmlspecialchars($book['story'] ?? '') ?>" target="_blank" class="btn" style="padding:14px 30px; display:flex; align-items:center; gap:8px;">
                            <i data-lucide="book-open" style="width:18px;height:18px;"></i> Read Now
                        </a>
                    <?php else: ?>
                        <a href="downloadBook.php?bid=<?= $bid ?>" class="btn" style="padding:14px 30px; display:flex; align-items:center; gap:8px;">
                            <i data-lucide="download" style="width:18px;height:18px;"></i> Download & Read
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="action-panel paid">
                <div>
                    <h3 style="margin:0 0 4px; color:#f59e0b;">Premium Edition</h3>
                    <p style="margin:0; font-size:13px; color:var(--text-muted);">Purchase once to unlock permanent access.</p>
                </div>
                <div style="margin-left:auto; display:flex; align-items:center; gap:20px;">
                    <div style="font-size:24px; font-weight:800; color:#f59e0b;">₹<?= htmlspecialchars($book['price'] ?? '') ?></div>
                    <?php if ($purchased): ?>
                        <a href="downloadBook.php?bid=<?= $bid ?>" class="btn" style="padding:14px 30px; background:#f59e0b; color:#fff; display:flex; align-items:center; gap:8px;">
                            <i data-lucide="download" style="width:18px;height:18px;"></i> Download Book
                        </a>
                        <a href="../uploadpdf/<?= htmlspecialchars($book['story'] ?? '') ?>" target="_blank" class="btn-outline" style="padding:14px 30px; display:flex; align-items:center; gap:8px; border-color:rgba(245,158,11,0.3); color:#f59e0b;">
                            <i data-lucide="book-open" style="width:18px;height:18px;"></i> Read Online
                        </a>
                    <?php else: ?>
                        <a href="purchase.php?bid=<?= $bid ?>" class="btn" style="padding:14px 30px; background:#f59e0b; color:#fff; display:flex; align-items:center; gap:8px;">
                            <i data-lucide="shopping-bag" style="width:18px;height:18px;"></i> Purchase Now
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div style="border-top:1px solid var(--border); padding-top:40px;">
    <h2 style="font-size:24px; margin-bottom:30px;">Reviews & Ratings</h2>
    
    <div style="display:grid; grid-template-columns: 1fr 2fr; gap:40px;">
        <?php if ($book['is_paid'] == 'free' || $purchased): ?>
        <div>
            <div class="glass-card">
                <h3 style="margin-top:0; margin-bottom:20px; font-size:18px;"><?= $myReview ? 'Update Your Review' : 'Write a Review' ?></h3>
                <form method="POST">
                    <label>Rating</label>
                    <select name="rating" class="form-control" style="margin-bottom:16px;" required>
                        <option value="">Select...</option>
                        <?php for($r=5;$r>=1;$r--): ?>
                            <option value="<?= $r ?>" <?= ($myReview && $myReview[0]['rating']==$r)?'selected':'' ?>><?= $r ?> Stars</option>
                        <?php endfor; ?>
                    </select>
                    <label>Your Review</label>
                    <textarea name="review_text" class="form-control" rows="4" style="margin-bottom:16px; resize:vertical;" required><?= $myReview ? htmlspecialchars($myReview[0]['review_text']) : '' ?></textarea>
                    <button type="submit" name="btn_review" class="btn" style="width:100%; padding:12px;">Submit Review</button>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div>
            <div class="glass-card" style="text-align:center; padding:40px;">
                <p style="color:var(--text-muted); margin:0;">You must purchase this book to leave a review.</p>
            </div>
        </div>
        <?php endif; ?>

        <div>
            <?php if ($reviews): foreach($reviews as $rev): ?>
            <div class="review-card">
                <div class="review-header">
                    <div>
                        <strong style="font-size:15px;"><?= htmlspecialchars($rev['username'] ?? '') ?></strong>
                        <div class="rating-stars" style="margin-top:4px; display:flex; gap:2px;">
                            <?php for($s=1;$s<=5;$s++): ?>
                                <i data-lucide="star" style="width:12px;height:12px; <?= $s <= $rev['rating'] ? 'fill:currentColor;' : 'opacity:0.3;' ?>"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <span style="font-size:12px; color:var(--text-muted);"><?= date('d M Y', strtotime($rev['review_date'])) ?></span>
                </div>
                <p style="color:var(--text-muted); line-height:1.6; margin:0;"><?= nl2br(htmlspecialchars($rev['review_text'] ?? '')) ?></p>
            </div>
            <?php endforeach; else: ?>
            <div style="padding:40px; text-align:center; color:var(--text-muted); border:1px dashed var(--border); border-radius:12px;">No reviews yet. Be the first to share your thoughts!</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- FEEDBACK SECTION -->
<?php if ($downloaded || $purchased || $book['is_paid'] == 'free'): ?>
<div id="feedback" style="border-top:1px solid var(--border); padding-top:40px; margin-top:40px;">
    <h2 style="font-size:24px; margin-bottom:8px; display:flex; align-items:center; gap:10px;">
        <i data-lucide="message-square" style="width:24px;height:24px;stroke:var(--accent);"></i>
        Send Feedback About This Book
    </h2>
    <p style="color:var(--text-muted); font-size:14px; margin-bottom:30px;">Share your thoughts, suggestions, or concerns about <strong><?= htmlspecialchars($book['bname'] ?? '') ?></strong> with the admin team.</p>

    <?php if ($msg): ?>
    <div style="padding:12px 16px; background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.2); border-radius:8px; margin-bottom:20px; font-size:14px;">
        <?= htmlspecialchars($msg) ?>
    </div>
    <?php endif; ?>

    <div class="glass-card" style="max-width:680px;">
        <form method="POST" action="bookDetail.php?bid=<?= $bid ?>#feedback">
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:13px; font-weight:600; color:var(--text-muted); margin-bottom:8px; text-transform:uppercase; letter-spacing:0.5px;">Subject</label>
                <input type="text" name="fb_subject"
                    class="form-control"
                    value="Feedback: <?= htmlspecialchars($book['bname'] ?? '') ?>"
                    placeholder="What is your feedback about?"
                    style="margin-bottom:0;"
                    required>
            </div>
            <div style="margin-bottom:20px;">
                <label style="display:block; font-size:13px; font-weight:600; color:var(--text-muted); margin-bottom:8px; text-transform:uppercase; letter-spacing:0.5px;">Your Message</label>
                <textarea name="fb_message"
                    class="form-control"
                    rows="5"
                    style="resize:vertical; margin-bottom:0;"
                    placeholder="Tell us what you think about this book — content quality, suggestions, issues..."
                    required><?= isset($_POST['fb_message']) ? htmlspecialchars($_POST['fb_message'] ?? '') : '' ?></textarea>
            </div>
            <div style="display:flex; gap:12px; align-items:center;">
                <button type="submit" name="btn_feedback" class="btn" style="padding:12px 28px; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="send" style="width:16px;height:16px;"></i>
                    Send Feedback
                </button>
                <a href="complaints.php?bid=<?= $bid ?>&type=Book+Content" style="font-size:13px; color:#ef4444; text-decoration:none; display:flex; align-items:center; gap:6px;">
                    <i data-lucide="flag" style="width:14px;height:14px;"></i> Report an issue instead
                </a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php include("readerFooter.php"); ?>
