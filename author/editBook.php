<?php
require('../config/autoload.php');
if (!isset($_SESSION['authid'])) { header('location:login.php'); exit(); }

$dao        = new DataAccess();
$file       = new FileUpload();
$authid     = $_SESSION['authid'];
$authorName = $_SESSION['authname'];

$bid = isset($_GET['bid']) ? intval($_GET['bid']) : 0;
if (!$bid) { header('location:viewbooks.php'); exit(); }

// Verify the book belongs to this author
$safeAuthor = addslashes($authorName);
$bookRows   = $dao->query("SELECT b.*, c.cname FROM books b LEFT JOIN category c ON b.cid=c.cid WHERE b.bid='$bid' AND b.AuthorName='$safeAuthor' AND b.status=1");
if (!$bookRows) { header('location:viewbooks.php'); exit(); }
$book = $bookRows[0];

$msg     = '';
$success = '';

if (isset($_POST['btn_update'])) {
    $bname       = trim($_POST['bname']);
    $description = trim($_POST['description']);
    $cid         = intval($_POST['cid']);
    $is_paid     = $_POST['is_paid'];
    $price       = ($is_paid === 'free') ? 0 : intval($_POST['price']);

    $errors = [];
    if (strlen($bname) < 2)       $errors[] = 'Book name must be at least 2 characters.';
    if (strlen($description) < 5) $errors[] = 'Description is too short.';
    if (!$cid)                     $errors[] = 'Please select a category.';
    if ($is_paid === 'paid' && $price <= 0) $errors[] = 'Enter a valid price for paid books.';

    if (empty($errors)) {
        $coverImage = $book['CoverImage'];
        $storyFile  = $book['story'];

        // Optional new cover image
        if (!empty($_FILES['CoverImage']['name'])) {
            $newCover = $file->doUploadRandom($_FILES['CoverImage'], ['.jpg','.png','.jpeg'], 2000000, 1, '../uploads');
            if ($newCover) $coverImage = $newCover;
            else           $errors[] = 'Cover image upload failed: ' . $file->errors();
        }
        // Optional new PDF
        if (!empty($_FILES['story']['name'])) {
            $newPdf = $file->doUploadRandom($_FILES['story'], ['.pdf'], 5000000, 1, '../uploadpdf');
            if ($newPdf) $storyFile = $newPdf;
            else         $errors[] = 'PDF upload failed: ' . $file->errors();
        }

        if (empty($errors)) {
            $updated = $dao->update([
                'bname'       => $bname,
                'description' => $description,
                'cid'         => $cid,
                'CoverImage'  => $coverImage,
                'story'       => $storyFile,
                'is_paid'     => $is_paid,
                'price'       => $price,
            ], 'books', "bid='$bid'");

            if ($updated) {
                $success = 'Book updated successfully!';
                // Refresh book data
                $bookRows = $dao->query("SELECT b.*, c.cname FROM books b LEFT JOIN category c ON b.cid=c.cid WHERE b.bid='$bid'");
                $book = $bookRows[0];
            } else {
                $msg = 'Update failed. Please try again.';
            }
        }
    }
    if (!empty($errors)) $msg = implode('<br>', $errors);
}

// Delete
if (isset($_GET['delete']) && $_GET['delete'] == 1) {
    $dao->update(['status' => 0], 'books', "bid='$bid' AND AuthorName='$safeAuthor'");
    echo "<script>alert('Book removed successfully.');window.location.href='viewbooks.php';</script>";
    exit();
}

$categories = $dao->query("SELECT cid, cname FROM category WHERE status=1 ORDER BY cname");

include("header.php");
?>
<style>
    .edit-grid { display: grid; grid-template-columns: 260px 1fr; gap: 32px; align-items: start; }
    .preview-card { background: var(--card); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; }
    .preview-cover { width: 100%; height: 340px; object-fit: cover; display: block; }
    .preview-body { padding: 20px; }
    .field-group { margin-bottom: 20px; }
    .field-group label { display: block; font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
    .radio-row { display: flex; gap: 16px; align-items: center; }
    .radio-row label { font-size: 14px; text-transform: none; letter-spacing: 0; color: var(--text-main); font-weight: 500; display: flex; align-items: center; gap: 6px; cursor: pointer; }
    .optional-tag { font-size: 10px; background: rgba(99,102,241,0.15); color: #818cf8; padding: 1px 7px; border-radius: 10px; margin-left: 6px; text-transform: none; letter-spacing: 0; font-weight: 600; }
</style>

<div style="margin-bottom:24px; display:flex; align-items:center; gap:16px;">
    <a href="viewbooks.php" class="btn" style="background:rgba(255,255,255,0.06); color:var(--text-main); border:1px solid var(--border); padding:8px 16px; font-size:13px; display:flex; align-items:center; gap:6px;">
        <i data-lucide="arrow-left" style="width:15px;height:15px;"></i> Back to My Books
    </a>
    <div>
        <div style="font-size:18px; font-weight:700;">Edit Book</div>
        <div style="font-size:13px; color:var(--text-muted); margin-top:2px;"><?= htmlspecialchars($book['bname']) ?></div>
    </div>
</div>

<?php if ($success): ?>
<div style="padding:14px 18px; background:rgba(16,185,129,0.1); color:#10b981; border:1px solid rgba(16,185,129,0.25); border-radius:10px; margin-bottom:24px; display:flex; align-items:center; gap:10px;">
    <i data-lucide="check-circle" style="width:18px;height:18px;"></i> <?= $success ?>
</div>
<?php endif; ?>
<?php if ($msg): ?>
<div style="padding:14px 18px; background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.25); border-radius:10px; margin-bottom:24px;">
    <?= $msg ?>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
<div class="edit-grid">

    <!-- Left: Cover Preview + file uploads -->
    <div>
        <div class="preview-card">
            <img src="../uploads/<?= htmlspecialchars($book['CoverImage']) ?>" class="preview-cover" id="coverPreview" onerror="this.src='../uploads/placeholder.png'" alt="Cover">
            <div class="preview-body">
                <div style="font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:12px; font-weight:600;">Current Cover</div>
                <label style="display:block; cursor:pointer; background:rgba(99,102,241,0.1); border:1px dashed rgba(99,102,241,0.35); color:#818cf8; padding:12px; border-radius:8px; text-align:center; font-size:13px; font-weight:500;">
                    <i data-lucide="image" style="width:16px;height:16px;display:inline;vertical-align:middle;margin-right:6px;"></i>
                    Replace Cover Image
                    <input type="file" name="CoverImage" accept=".jpg,.jpeg,.png" style="display:none;" onchange="previewCover(this)">
                </label>
                <div id="newCoverName" style="font-size:11px; color:#818cf8; margin-top:6px; text-align:center;"></div>
            </div>
        </div>

        <div style="margin-top:16px; background:var(--card); border:1px solid var(--border); border-radius:12px; padding:18px;">
            <div style="font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:12px; font-weight:600;">Story PDF</div>
            <a href="../uploadpdf/<?= htmlspecialchars($book['story']) ?>" target="_blank" style="display:flex; align-items:center; gap:8px; color:var(--accent); font-size:13px; text-decoration:none; margin-bottom:12px;">
                <i data-lucide="file-text" style="width:15px;height:15px;"></i> View Current PDF
            </a>
            <label style="display:block; cursor:pointer; background:rgba(99,102,241,0.1); border:1px dashed rgba(99,102,241,0.35); color:#818cf8; padding:12px; border-radius:8px; text-align:center; font-size:13px; font-weight:500;">
                <i data-lucide="upload" style="width:16px;height:16px;display:inline;vertical-align:middle;margin-right:6px;"></i>
                Replace PDF <span class="optional-tag">optional</span>
                <input type="file" name="story" accept=".pdf" style="display:none;" onchange="document.getElementById('pdfName').textContent=this.files[0]?.name||''">
            </label>
            <div id="pdfName" style="font-size:11px; color:#818cf8; margin-top:6px; text-align:center;"></div>
        </div>

        <!-- Danger Zone -->
        <div style="margin-top:16px; background:rgba(239,68,68,0.05); border:1px solid rgba(239,68,68,0.2); border-radius:12px; padding:18px;">
            <div style="font-size:12px; font-weight:700; color:#ef4444; margin-bottom:10px; display:flex; align-items:center; gap:6px;">
                <i data-lucide="alert-triangle" style="width:15px;height:15px;"></i> Danger Zone
            </div>
            <p style="font-size:13px; color:var(--text-muted); margin-bottom:14px; line-height:1.6;">Deleting this book will remove it from the library. This cannot be undone.</p>
            <a href="editBook.php?bid=<?= $bid ?>&delete=1" class="btn" style="background:rgba(239,68,68,0.12); color:#ef4444; border:1px solid rgba(239,68,68,0.3); width:100%; justify-content:center; gap:6px;" onclick="return confirm('Are you sure you want to delete this book? This cannot be undone.')">
                <i data-lucide="trash-2" style="width:15px;height:15px;"></i> Delete This Book
            </a>
        </div>
    </div>

    <!-- Right: Edit Fields -->
    <div class="glass-card" style="margin:0;">
        <h3 style="margin-top:0; margin-bottom:24px; font-size:17px; display:flex; align-items:center; gap:8px;">
            <i data-lucide="pencil" style="width:18px;height:18px;stroke:var(--accent);"></i> Book Details
        </h3>

        <div class="field-group">
            <label>Book Title</label>
            <input type="text" name="bname" class="form-control" value="<?= htmlspecialchars($book['bname']) ?>" required>
        </div>

        <div class="field-group">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="5" style="resize:vertical;" required><?= htmlspecialchars($book['description']) ?></textarea>
        </div>

        <div class="field-group">
            <label>Category</label>
            <select name="cid" class="form-control" required>
                <option value="">Select category...</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['cid'] ?>" <?= $book['cid'] == $cat['cid'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['cname']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field-group">
            <label>Book Type</label>
            <div class="radio-row">
                <label>
                    <input type="radio" name="is_paid" value="free" <?= $book['is_paid'] === 'free' ? 'checked' : '' ?> onchange="togglePrice(this.value)"> Free
                </label>
                <label>
                    <input type="radio" name="is_paid" value="paid" <?= $book['is_paid'] === 'paid' ? 'checked' : '' ?> onchange="togglePrice(this.value)"> Paid
                </label>
            </div>
        </div>

        <div class="field-group" id="priceDiv" style="<?= $book['is_paid'] === 'free' ? 'display:none;' : '' ?>">
            <label>Price (₹)</label>
            <input type="number" name="price" class="form-control" value="<?= htmlspecialchars($book['price']) ?>" min="1" placeholder="e.g. 299">
        </div>

        <div style="display:flex; gap:12px; margin-top:8px;">
            <button type="submit" name="btn_update" class="btn" style="flex:1; justify-content:center; gap:8px; padding:13px;">
                <i data-lucide="save" style="width:16px;height:16px;"></i> Save Changes
            </button>
            <a href="viewbooks.php" class="btn" style="background:rgba(255,255,255,0.06); color:var(--text-main); border:1px solid var(--border); padding:13px 20px;">Cancel</a>
        </div>
    </div>
</div>
</form>

<script>
function togglePrice(val) {
    document.getElementById('priceDiv').style.display = (val === 'free') ? 'none' : 'block';
}
function previewCover(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('coverPreview').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
        document.getElementById('newCoverName').textContent = input.files[0].name;
    }
}
</script>

<?php include("footer.php"); ?>
