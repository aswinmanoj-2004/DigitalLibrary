<?php
require('../config/autoload.php');
include("header.php");

$file = new FileUpload();
$dao  = new DataAccess();
$msg  = '';

// ── DETERMINE MODE: edit or add ──
$editMode = false;
$editCat  = null;
if (isset($_GET['cid'])) {
    $cid  = intval($_GET['cid']);
    $rows = $dao->query("SELECT * FROM category WHERE cid='$cid' AND status=1");
    if ($rows) {
        $editMode = true;
        $editCat  = $rows[0];
    }
}

// ── INSERT ──
if (isset($_POST['btn_insert'])) {
    $cname = trim($_POST['cname']);
    $cdesc = trim($_POST['cdesc']);
    $errors = [];
    if (strlen($cname) < 3)  $errors[] = 'Category name must be at least 3 characters.';
    if (strlen($cdesc) < 4)  $errors[] = 'Description must be at least 4 characters.';
    if (empty($_FILES['cimage']['name'])) $errors[] = 'Category image is required.';

    if (empty($errors)) {
        $fileName = $file->doUploadRandom($_FILES['cimage'], ['.jpg','.png','.jpeg'], 1000000, 1, '../uploads');
        if ($fileName) {
            if ($dao->insert(['cname'=>$cname,'cdesc'=>$cdesc,'cimage'=>$fileName], 'category')) {
                echo "<script>alert('Category added successfully!'); window.location.href='Category.php';</script>"; exit();
            } else { $msg = 'Database insertion failed.'; }
        } else { $msg = $file->errors(); }
    } else { $msg = implode('<br>', $errors); }
}

// ── UPDATE ──
if (isset($_POST['btn_update'])) {
    $cid   = intval($_POST['cid']);
    $cname = trim($_POST['cname']);
    $cdesc = trim($_POST['cdesc']);
    $errors = [];
    if (strlen($cname) < 3) $errors[] = 'Category name must be at least 3 characters.';
    if (strlen($cdesc) < 4) $errors[] = 'Description must be at least 4 characters.';

    if (empty($errors)) {
        $updateData = ['cname' => $cname, 'cdesc' => $cdesc];

        // Optional new image
        if (!empty($_FILES['cimage']['name'])) {
            $newImg = $file->doUploadRandom($_FILES['cimage'], ['.jpg','.png','.jpeg'], 1000000, 1, '../uploads');
            if ($newImg) $updateData['cimage'] = $newImg;
            else { $msg = 'Image upload failed: ' . $file->errors(); }
        }

        if (!$msg) {
            if ($dao->update($updateData, 'category', "cid='$cid'")) {
                echo "<script>alert('Category updated successfully!'); window.location.href='Category.php';</script>"; exit();
            } else { $msg = 'Update failed. Please try again.'; }
        }
    } else { $msg = implode('<br>', $errors); }
}
?>

<style>
    .cat-form-grid { display: grid; grid-template-columns: 200px 1fr; gap: 28px; align-items: start; }
    .img-preview-wrap { background: var(--card); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
    .img-preview { width: 100%; height: 160px; object-fit: cover; display: block; }
    .img-upload-label { display: block; cursor: pointer; margin: 14px; background: rgba(239,68,68,0.08); border: 1px dashed rgba(239,68,68,0.3); color: var(--accent); padding: 10px; border-radius: 8px; text-align: center; font-size: 13px; font-weight: 500; }
    .field-g { margin-bottom: 18px; }
    .field-g label { display: block; font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 7px; }
    .cat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px; }
    .cat-card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; transition: border-color 0.2s, transform 0.2s; }
    .cat-card:hover { border-color: var(--accent); transform: translateY(-2px); }
    .cat-cover { width: 100%; height: 130px; object-fit: cover; display: block; }
    .cat-body { padding: 14px 16px; }
    .cat-name { font-size: 15px; font-weight: 700; margin-bottom: 4px; }
    .cat-desc { font-size: 12px; color: var(--text-muted); line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 14px; }
    .cat-actions { display: flex; gap: 8px; }
</style>

<!-- ── ADD / EDIT FORM ── -->
<div class="section" style="max-width:700px; margin-bottom:32px;">
    <h2 style="display:flex;align-items:center;gap:8px; margin-bottom:20px;">
        <i data-lucide="<?= $editMode ? 'pencil' : 'plus-circle' ?>" style="width:20px;height:20px;stroke:var(--accent);"></i>
        <?= $editMode ? 'Edit Category' : 'Add New Category' ?>
    </h2>

    <?php if ($msg): ?>
    <div class="alert alert-danger" style="margin-bottom:18px;"><?= $msg ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <?php if ($editMode): ?>
        <input type="hidden" name="cid" value="<?= $editCat['cid'] ?>">
        <?php endif; ?>

        <div class="cat-form-grid">
            <!-- Image preview -->
            <div>
                <div class="img-preview-wrap">
                    <img id="catImgPreview"
                         src="<?= $editMode ? '../uploads/'.htmlspecialchars($editCat['cimage']) : '' ?>"
                         class="img-preview"
                         style="<?= $editMode ? '' : 'display:none;' ?>"
                         onerror="this.style.display='none'">
                    <label class="img-upload-label">
                        <i data-lucide="image" style="width:15px;height:15px;display:inline;vertical-align:middle;margin-right:5px;"></i>
                        <?= $editMode ? 'Replace Image' : 'Upload Image' ?>
                        <?php if ($editMode): ?>
                        <span style="font-size:10px;background:rgba(99,102,241,0.15);color:#818cf8;padding:1px 6px;border-radius:10px;margin-left:4px;">optional</span>
                        <?php endif; ?>
                        <input type="file" name="cimage" accept=".jpg,.jpeg,.png" style="display:none;" onchange="previewCatImg(this)" <?= $editMode ? '' : 'required' ?>>
                    </label>
                    <div id="catFileName" style="font-size:11px;color:var(--accent);text-align:center;padding:0 14px 10px;"></div>
                </div>
            </div>

            <!-- Fields -->
            <div>
                <div class="field-g">
                    <label>Category Name</label>
                    <input type="text" name="cname" class="form-control"
                           value="<?= $editMode ? htmlspecialchars($editCat['cname']) : '' ?>"
                           placeholder="e.g. Science Fiction" required>
                </div>
                <div class="field-g">
                    <label>Description</label>
                    <textarea name="cdesc" class="form-control" rows="4" style="resize:vertical;" placeholder="Brief description of this category..." required><?= $editMode ? htmlspecialchars($editCat['cdesc']) : '' ?></textarea>
                </div>
                <div style="display:flex; gap:10px;">
                    <?php if ($editMode): ?>
                    <button type="submit" name="btn_update" class="btn btn-primary" style="flex:1;justify-content:center;gap:8px;">
                        <i data-lucide="save" style="width:15px;height:15px;"></i> Save Changes
                    </button>
                    <a href="Category.php" class="btn btn-secondary">Cancel</a>
                    <?php else: ?>
                    <button type="submit" name="btn_insert" class="btn btn-primary" style="flex:1;justify-content:center;gap:8px;">
                        <i data-lucide="plus" style="width:15px;height:15px;"></i> Add Category
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- ── CATEGORY GRID ── -->
<div class="section">
    <h2 style="display:flex;align-items:center;gap:8px; margin-bottom:20px;">
        <i data-lucide="layout-grid" style="width:20px;height:20px;stroke:var(--accent);"></i> All Categories
    </h2>

    <?php
    $categories = $dao->query("SELECT * FROM category WHERE status=1 ORDER BY cid ASC");
    if ($categories):
    ?>
    <div class="cat-grid">
        <?php foreach ($categories as $cat): ?>
        <div class="cat-card">
            <img src="../uploads/<?= htmlspecialchars($cat['cimage']) ?>"
                 class="cat-cover"
                 onerror="this.src='../uploads/placeholder.png'"
                 alt="<?= htmlspecialchars($cat['cname']) ?>">
            <div class="cat-body">
                <div class="cat-name"><?= htmlspecialchars($cat['cname']) ?></div>
                <div class="cat-desc"><?= htmlspecialchars($cat['cdesc']) ?></div>
                <div class="cat-actions">
                    <a href="Category.php?cid=<?= $cat['cid'] ?>" class="btn btn-warning btn-sm" style="flex:1;justify-content:center;gap:6px;">
                        <i data-lucide="pencil" style="width:13px;height:13px;"></i> Edit
                    </a>
                    <a href="deleteCategory.php?cid=<?= $cat['cid'] ?>"
                       class="btn btn-danger btn-sm"
                       style="gap:6px;"
                       onclick="return confirm('Delete category \'<?= htmlspecialchars(addslashes($cat['cname'])) ?>\'? Books in this category will be uncategorized.')">
                        <i data-lucide="trash-2" style="width:13px;height:13px;"></i> Delete
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div style="padding:60px;text-align:center;color:var(--text-muted);border:1px dashed var(--border);border-radius:12px;">
        No categories yet. Add your first one above.
    </div>
    <?php endif; ?>
</div>

<script>
function previewCatImg(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.getElementById('catImgPreview');
            img.src = e.target.result;
            img.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
        document.getElementById('catFileName').textContent = input.files[0].name;
    }
}
</script>

<?php include("footer.php"); ?>