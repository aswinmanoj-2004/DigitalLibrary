<?php
require('../config/autoload.php');
if (!isset($_SESSION['authid'])) { header('location:login.php'); exit(); }

$file    = new FileUpload();
$filePDF = new FileUpload();
$dao     = new DataAccess();

$elements = array(
    "bname"=>"","AuthorName"=>$_SESSION['authname'] ?? "","description"=>"","cid"=>"",
    "CoverImage"=>"","story"=>"","is_paid"=>"","price"=>"","AddedDate"=>""
);

$form      = new FormAssist($elements, $_POST);
$labels    = array(
    'bname'=>"Book Name",'AuthorName'=>"Author Name",'description'=>"Description",
    'cid'=>"Category",'CoverImage'=>"Book Image",'story'=>"Story PDF",
    'is_paid'=>"Paid/Free",'price'=>"Price",'AddedDate'=>"Added Date"
);
$rules = array(
    "bname"      => array("required"=>true,"minlength"=>2),
    "AuthorName" => array("required"=>true),
    "description"=> array("required"=>true),
    "cid"        => array("required"=>true),
    "CoverImage" => array("filerequired"=>true),
    "story"      => array("filerequired"=>true),
    "is_paid"    => array("required"=>true),
    "price"      => array("required"=>true),
    "AddedDate"  => array("required"=>true)
);

$validator = new FormValidator($rules, $labels);
$msg = "";

// ── INSERT ──
if (isset($_POST['btn_insert'])) {
    // Inject default price for free books before validation so it passes the 'required' rule
    if (isset($_POST['is_paid']) && $_POST['is_paid'] == 'free') {
        $_POST['price'] = '0';
    }

    if ($validator->validate($_POST)) {
        if ($_POST['is_paid'] == "free") { $_POST['price'] = 0; }
        elseif (empty($_POST['price'])) { $msg = "Price is required for paid books."; }

        if ($msg == "") {
            $fileName = $file->doUploadRandom($_FILES['CoverImage'], array('.jpg','.png','.jpeg'), 1000000, 1, '../uploads');
            $pdfName  = $file->doUploadRandom($_FILES['story'],      array('.pdf'),               5000000, 1, '../uploadpdf');

            if ($fileName && $pdfName !== false) {
                $data = array(
                    'bname'       => $_POST['bname'],
                    'AuthorName'  => $_POST['AuthorName'],
                    'description' => $_POST['description'],
                    'cid'         => $_POST['cid'],
                    'CoverImage'  => $fileName,
                    'story'       => $pdfName,
                    'is_paid'     => $_POST['is_paid'],
                    'price'       => $_POST['price'],
                    'AddedDate'   => $_POST['AddedDate']
                );
                if ($dao->insert($data, "books")) {
                    echo "<script>alert('Book published successfully!'); window.location.href='Books.php';</script>"; exit();
                } else { $msg = "Database insertion failed!"; }
            } else { $msg = $file->errors() . " " . $filePDF->errors(); }
        }
    }
}

// ── DELETE ──
if (isset($_GET['delete'])) {
    $bid = intval($_GET['delete']);
    $safeAuthor = addslashes($_SESSION['authname'] ?? '');
    // Use update() not query() — query() crashes on non-SELECT results
    $dao->update(['status' => 0], 'books', "bid='$bid' AND AuthorName='$safeAuthor'");
    echo "<script>alert('Book removed successfully.'); window.location.href='Books.php';</script>"; exit();
}

$categories = $dao->query("SELECT cid, cname FROM category WHERE status=1 ORDER BY cname");

include("header.php");
?>

<style>
/* ── FORM LAYOUT ── */
.book-form-grid {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 28px;
    align-items: start;
}

/* ── DROPZONES ── */
.dropzone {
    position: relative;
    border: 2px dashed var(--border);
    border-radius: 14px;
    background: rgba(255,255,255,0.02);
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    cursor: pointer;
    transition: all 0.25s ease;
    overflow: hidden;
    text-align: center;
}
.dropzone:hover, .dropzone.dragover {
    border-color: var(--accent);
    background: rgba(84, 104, 255, 0.05);
}
.dropzone input[type="file"] {
    position: absolute; inset: 0;
    opacity: 0; cursor: pointer; width: 100%; height: 100%;
}
.dropzone-cover {
    width: 100%; min-height: 300px;
}
.cover-preview {
    width: 100%; height: 100%; min-height: 300px;
    object-fit: cover; display: none;
    border-radius: 12px;
}
.dropzone-icon { margin-bottom: 12px; color: var(--text-muted); transition: color 0.2s; }
.dropzone:hover .dropzone-icon { color: var(--accent); }
.dropzone-title { font-size: 14px; font-weight: 600; color: var(--text-main); margin-bottom: 4px; }
.dropzone-sub { font-size: 12px; color: var(--text-muted); }
.dropzone-badge {
    margin-top: 10px; font-size: 11px; font-weight: 600;
    padding: 4px 12px; border-radius: 20px;
    background: rgba(82, 97, 255, 0.12); color: var(--accent);
}
.dz-selected-name {
    margin-top: 8px; font-size: 12px; font-weight: 500;
    color: var(--accent); word-break: break-all; padding: 0 10px;
}

/* ── PDF DROPZONE ── */
.dropzone-pdf {
    padding: 28px 20px; border-radius: 12px;
    border: 2px dashed var(--border);
    background: rgba(255,255,255,0.02);
    position: relative; cursor: pointer;
    transition: all 0.25s; display: flex;
    align-items: center; gap: 16px;
    margin-bottom: 20px;
}
.dropzone-pdf:hover { border-color: var(--accent); background: rgba(77, 151, 255, 0.04); }
.dropzone-pdf input[type="file"] { position:absolute; inset:0; opacity:0; cursor:pointer; }
.pdf-icon-wrap {
    width: 52px; height: 52px; flex-shrink: 0;
    background: rgba(101, 132, 255, 0.1); border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
}

/* ── CUSTOM CATEGORY SELECT ── */
.cat-select-wrap { position: relative; }
.cat-select-wrap select.form-control {
    appearance: none; -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%23a1a1aa' viewBox='0 0 24 24'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    padding-right: 40px;
    cursor: pointer;
    height: 46px;
}
.cat-select-wrap::before {
    content: '';
    position: absolute; left: 14px; top: 50%;
    transform: translateY(-50%);
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--accent); pointer-events: none;
}
.cat-select-wrap select { padding-left: 30px; }

/* ── TYPE TOGGLE (radio as pills) ── */
.type-toggle { display: flex; gap: 0; border-radius: 10px; overflow: hidden; border: 1px solid var(--border); width: fit-content; }
.type-toggle input[type="radio"] { display: none; }
.type-toggle label {
    padding: 10px 28px; font-size: 14px; font-weight: 600;
    cursor: pointer; transition: all 0.2s;
    color: var(--text-muted); background: rgba(255,255,255,0.03);
    display: flex; align-items: center; gap: 8px;
    user-select: none;
}
.type-toggle input[type="radio"]:checked + label {
    background: var(--accent); color: #fff;
}
.type-toggle label:first-of-type { border-right: 1px solid var(--border); }

/* ── DATE INPUT ── */
input[type="date"].form-control {
    cursor: pointer;
    color-scheme: dark;
    height: 46px;
}
input[type="date"].form-control::-webkit-calendar-picker-indicator {
    filter: invert(0.7);
    cursor: pointer;
    padding: 4px;
}

/* ── SECTION DIVIDER ── */
.form-divider {
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 1.2px; color: var(--text-muted);
    margin: 24px 0 16px; padding-bottom: 8px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: 8px;
}

/* ── FIELD GROUP ── */
.fg { margin-bottom: 18px; }
.fg > label {
    display: block; font-size: 12px; font-weight: 600;
    color: var(--text-muted); text-transform: uppercase;
    letter-spacing: 0.5px; margin-bottom: 8px;
}
.fg .err { font-size: 12px; color: #ef4444; margin-top: 5px; min-height: 16px; }

/* ── PRICE BOX ── */
.price-wrap { position: relative; }
.price-wrap .price-symbol {
    position: absolute; left: 14px; top: 50%;
    transform: translateY(-50%);
    font-size: 15px; font-weight: 700;
    color: var(--accent); pointer-events: none;
}
.price-wrap input { padding-left: 32px; }

/* ── TABLE WRAP & AUTHOR SPECIFICS ── */
.table-wrap { overflow-x:auto; margin-top:6px; }
table img { width:48px; height:65px; object-fit:cover; border-radius:5px; border:1px solid var(--border); }
.action-link { font-size:12px; font-weight:600; text-decoration:none; padding:4px 10px; border-radius:5px; }
.act-delete { color:#ef4444; background:rgba(239,68,68,0.08); }
.act-delete:hover { background:rgba(239,68,68,0.18); }
</style>

<script>
function showFileName(input, id) {
    if (input.files[0]) document.getElementById(id).innerText = "📄 " + input.files[0].name;
}
</script>

<!-- ── UPLOAD FORM ── -->
<div class="glass-card" style="margin-bottom:28px;">
    <h2 style="display:flex;align-items:center;gap:8px; margin-bottom:24px; font-size:16px; font-weight:600;">
        <i data-lucide="plus-circle" style="width:20px;height:20px;stroke:var(--accent);"></i> Publish New Book
    </h2>

    <?php if ($msg != ""): ?>
    <div class="alert alert-danger" style="margin-bottom:20px; display:flex; align-items:center; gap:10px; color:#ef4444; font-size:13px; padding:10px 14px; background:rgba(239,68,68,0.08); border-radius:8px;">
        <i data-lucide="alert-circle" style="width:17px;height:17px;flex-shrink:0;"></i> <?= $msg ?>
    </div>
    <?php endif; ?>

    <form id="bookForm" action="" method="POST" enctype="multipart/form-data">
    <div class="book-form-grid">

        <!-- LEFT: Cover Image Dropzone -->
        <div>
            <div class="form-divider"><i data-lucide="image" style="width:14px;height:14px;"></i> Cover Image</div>
            <label class="dropzone dropzone-cover" id="coverZone">
                <input type="file" name="CoverImage" id="coverInput" accept=".jpg,.jpeg,.png" onchange="previewCover(this)">
                <img id="coverPreview" class="cover-preview" alt="Cover Preview">
                <div id="coverPlaceholder" style="padding:40px 20px;">
                    <div class="dropzone-icon"><i data-lucide="upload-cloud" style="width:40px;height:40px;"></i></div>
                    <div class="dropzone-title">Drop cover image here</div>
                    <div class="dropzone-sub">or click to browse</div>
                    <div class="dropzone-badge">JPG, PNG · Max 1MB</div>
                </div>
            </label>
            <div id="coverName" class="dz-selected-name"></div>
            <div style="color:#ef4444;font-size:12px;margin-top:5px;"><?= $validator->error('CoverImage') ?></div>

            <!-- PDF Dropzone below cover -->
            <div class="form-divider" style="margin-top:24px;"><i data-lucide="file-text" style="width:14px;height:14px;"></i> Story PDF</div>
            <label class="dropzone-pdf" id="pdfZone">
                <input type="file" name="story" id="pdfInput" accept=".pdf" onchange="selectPdf(this)">
                <div class="pdf-icon-wrap">
                    <i data-lucide="file-text" style="width:24px;height:24px;stroke:var(--accent);"></i>
                </div>
                <div>
                    <div id="pdfTitle" style="font-size:14px;font-weight:600;margin-bottom:3px;">Upload Story PDF</div>
                    <div id="pdfSub" style="font-size:12px;color:var(--text-muted);">PDF only · Max 5MB</div>
                </div>
                <i data-lucide="upload" style="width:18px;height:18px;stroke:var(--text-muted);margin-left:auto;flex-shrink:0;"></i>
            </label>
            <div style="color:#ef4444;font-size:12px;"><?= $validator->error('story') ?></div>
        </div>

        <!-- RIGHT: Book Details -->
        <div>
            <div class="form-divider"><i data-lucide="book-open" style="width:14px;height:14px;"></i> Book Details</div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="fg">
                    <label>Book Title</label>
                    <?= $form->textBox('bname', array('class'=>'form-control','placeholder'=>'e.g. The Lost World')); ?>
                    <div class="err"><?= $validator->error('bname') ?></div>
                </div>
                <div class="fg">
                    <label>Author Name</label>
                    <?= $form->textBox('AuthorName', array('class'=>'form-control','placeholder'=>'e.g. pen name')); ?>
                    <div class="err"><?= $validator->error('AuthorName') ?></div>
                </div>
            </div>

            <div class="fg">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="4" style="resize:vertical;" placeholder="A brief summary of the book..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                <div class="err"><?= $validator->error('description') ?></div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="fg">
                    <label>Category</label>
                    <div class="cat-select-wrap">
                        <select name="cid" class="form-control" required>
                            <option value="">Select a category...</option>
                            <?php if ($categories): foreach ($categories as $cat): ?>
                            <option value="<?= $cat['cid'] ?>" <?= isset($_POST['cid']) && $_POST['cid']==$cat['cid'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['cname']) ?>
                            </option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                    <div class="err"><?= $validator->error('cid') ?></div>
                </div>
                <div class="fg">
                    <label>Published Date</label>
                    <?= $form->inputBox('AddedDate', array('class'=>'form-control'), "date"); ?>
                    <div class="err"><?= $validator->error('AddedDate') ?></div>
                </div>
            </div>

            <div class="form-divider"><i data-lucide="tag" style="width:14px;height:14px;"></i> Pricing</div>

            <div class="fg">
                <label>Book Access Type</label>
                <div class="type-toggle">
                    <input type="radio" name="is_paid" id="type_free" value="free"
                           <?= (isset($_POST['is_paid']) && $_POST['is_paid']=='free') || !isset($_POST['is_paid']) ? 'checked' : '' ?>
                           onchange="togglePrice(this.value)">
                    <label for="type_free">
                        <i data-lucide="unlock" style="width:15px;height:15px;"></i> Free
                    </label>
                    <input type="radio" name="is_paid" id="type_paid" value="paid"
                           <?= isset($_POST['is_paid']) && $_POST['is_paid']=='paid' ? 'checked' : '' ?>
                           onchange="togglePrice(this.value)">
                    <label for="type_paid">
                        <i data-lucide="lock" style="width:15px;height:15px;"></i> Paid
                    </label>
                </div>
                <div class="err"><?= $validator->error('is_paid') ?></div>
            </div>

            <div id="priceDiv" class="fg" style="display:none;">
                <label>Price (₹)</label>
                <div class="price-wrap">
                    <span class="price-symbol">₹</span>
                    <?= $form->textBox('price', array('class'=>'form-control','placeholder'=>'e.g. 299','type'=>'number','min'=>'1')); ?>
                </div>
                <div class="err"><?= $validator->error('price') ?></div>
            </div>

            <button type="submit" name="btn_insert" class="btn btn-primary" style="width:100%;justify-content:center;gap:10px;padding:14px;font-size:15px;margin-top:8px;">
                <i data-lucide="upload-cloud" style="width:18px;height:18px;"></i> Publish Book
            </button>
        </div>
    </div>
    </form>
</div>

<!-- ── BOOK LIST ── -->
<div class="glass-card">
    <h2 style="font-size:16px;font-weight:600;margin-bottom:20px;">All Published Books</h2>
    <div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Cover</th>
                <th>Book Name</th>
                <th>Author</th>
                <th>Category</th>
                <th>Type</th>
                <th>Price</th>
                <th>PDF</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $authorName = addslashes($_SESSION['authname'] ?? '');
        $books = $dao->query("SELECT b.bid,b.bname,b.AuthorName,b.description,c.cname,b.CoverImage,b.story,b.is_paid,b.price,b.AddedDate FROM books b LEFT JOIN category c ON b.cid=c.cid WHERE b.status=1 AND b.AuthorName='$authorName' ORDER BY b.bid DESC");
        if (!empty($books)):
            $sl = 1;
            foreach ($books as $b):
        ?>
        <tr>
            <td style="color:var(--text-muted);"><?= $sl++ ?></td>
            <td><img src="../uploads/<?= htmlspecialchars($b['CoverImage']) ?>" onerror="this.src='../uploads/placeholder.png'"></td>
            <td style="font-weight:500;max-width:160px;"><?= htmlspecialchars($b['bname']) ?></td>
            <td style="color:var(--text-muted);"><?= htmlspecialchars($b['AuthorName']) ?></td>
            <td style="color:var(--text-muted);"><?= htmlspecialchars($b['cname'] ?? '—') ?></td>
            <td>
                <?php if ($b['is_paid']=='free'): ?>
                    <span class="badge badge-free">Free</span>
                <?php else: ?>
                    <span class="badge badge-paid">Paid</span>
                <?php endif; ?>
            </td>
            <td><?= $b['is_paid']=='free' ? '<span style="color:var(--text-muted);">—</span>' : '₹'.$b['price'] ?></td>
            <td>
                <a href="../uploadpdf/<?= htmlspecialchars($b['story']) ?>" target="_blank"
                   style="color:var(--accent);font-size:12px;text-decoration:none;background:var(--accent-soft);padding:4px 10px;border-radius:4px;">
                   View PDF
                </a>
            </td>
            <td style="color:var(--text-muted);white-space:nowrap;"><?= $b['AddedDate'] ?></td>
            <td style="display:flex; gap:8px; align-items:center; padding:12px;">
                <a href="editBook.php?bid=<?= $b['bid'] ?>" class="btn" style="padding:5px 12px; font-size:12px; display:flex; align-items:center; gap:5px;">
                    <i data-lucide="pencil" style="width:13px;height:13px;"></i> Edit
                </a>
                <a href="Books.php?delete=<?= $b['bid'] ?>"
                   class="action-link act-delete"
                   style="display:flex; align-items:center; gap:5px; padding:5px 10px;"
                   onclick="return confirm('Remove this book? This cannot be undone.')"><i data-lucide="trash-2" style="width:13px;height:13px;"></i> Delete</a>
            </td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="10" style="padding:30px;text-align:center;color:var(--text-muted);">No books published yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<script>
// ── Cover image preview ──
function previewCover(input) {
    if (!input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        const img = document.getElementById('coverPreview');
        img.src = e.target.result;
        img.style.display = 'block';
        document.getElementById('coverPlaceholder').style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
    document.getElementById('coverName').textContent = '✓ ' + input.files[0].name;
}

// ── PDF selection feedback ──
function selectPdf(input) {
    if (!input.files[0]) return;
    const name = input.files[0].name;
    document.getElementById('pdfTitle').textContent = '✓ ' + name;
    document.getElementById('pdfTitle').style.color = 'var(--accent)';
    document.getElementById('pdfSub').textContent = (input.files[0].size / 1024 / 1024).toFixed(2) + ' MB';
}

// ── Dragover effect for cover ──
const coverZone = document.getElementById('coverZone');
if (coverZone) {
    ['dragenter','dragover'].forEach(e => coverZone.addEventListener(e, () => coverZone.classList.add('dragover'), false));
    ['dragleave','drop'].forEach(e => coverZone.addEventListener(e, () => coverZone.classList.remove('dragover'), false));
}

// ── Price toggle ──
function togglePrice(val) {
    document.getElementById('priceDiv').style.display = (val === 'free') ? 'none' : 'block';
}

// ── Restore price state on validation fail ──
(function() {
    const checked = document.querySelector('input[name="is_paid"]:checked');
    if (checked) togglePrice(checked.value);
})();
</script>

<?php include("footer.php"); ?>