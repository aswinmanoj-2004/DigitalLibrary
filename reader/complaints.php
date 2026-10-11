<?php
require('../config/autoload.php');
if (!isset($_SESSION['reader_id'])) {
    header('location: readerLogin.php'); exit();
}

$dao      = new DataAccess();
$readerId = $_SESSION['reader_id'];

$elements = array("subject" => "", "description" => "");
$form     = new FormAssist($elements, $_POST);

$labels = array(
    "subject"     => "Subject",
    "description" => "Description",
);
$rules = array(
    "subject"     => array("required" => true, "minlength" => 5, "maxlength" => 100),
    "description" => array("required" => true, "minlength" => 10, "maxlength" => 1000),
);
$validator = new FormValidator($rules, $labels);

$msg = "";

// Pre-fill from GET params (e.g. coming from Purchases or Wishlist)
$prefillBid  = isset($_GET['bid'])  ? intval($_GET['bid'])        : 0;
$prefillType = isset($_GET['type']) ? htmlspecialchars($_GET['type']) : 'Other';

if (isset($_POST['btn_complaint'])) {
    if ($validator->validate($_POST)) {
        $data = [
            'userId'         => $readerId,
            'bid'            => isset($_POST['bid']) ? intval($_POST['bid']) : 0,
            'complaint_type' => $_POST['complaint_type'],
            'subject'        => $_POST['subject'],
            'description'    => $_POST['description'],
            'filed_date'     => date('Y-m-d H:i:s'),
            'status'         => 1,
        ];
        if ($dao->insert($data, "complaints")) {
            echo "<script>alert('Complaint submitted successfully!');</script>";
            header("location: complaints.php");
            exit();
        } else {
            $msg = "Failed to submit complaint. Please try again.";
        }
    }
}

include("readerHeader.php");

// My complaints
$complaints = $dao->query("
    SELECT c.*, b.bname
    FROM complaints c
    LEFT JOIN books b ON c.bid = b.bid
    WHERE c.userId='$readerId'
    ORDER BY c.filed_date DESC
");
?>
<div style="display:grid; grid-template-columns:1fr 2fr; gap:40px; align-items:start;">
    <div class="glass-card">
        <h3 style="margin-top:0; margin-bottom:20px; display:flex; align-items:center; gap:8px;">
            <i data-lucide="flag" style="width:20px;height:20px;stroke:#ef4444;"></i> Submit Complaint
        </h3>
        <?php if ($msg): ?><div style="padding:12px; background:rgba(239,68,68,0.1); color:#ef4444; border-radius:8px; margin-bottom:16px;"><?= $msg ?></div><?php endif; ?>
        <form method="POST">
            <!-- Carry over pre-fill values on submit -->
            <?php if ($prefillBid): ?>
            <input type="hidden" name="bid" value="<?= $prefillBid ?>">
            <?php if ($prefillBid): $prefillBook = $dao->query("SELECT bname FROM books WHERE bid='$prefillBid' LIMIT 1"); ?>
            <?php if ($prefillBook): ?>
            <div style="margin-bottom:16px; padding:10px 14px; background:rgba(0,229,160,0.07); border:1px solid rgba(0,229,160,0.2); border-radius:8px; font-size:13px; color:var(--accent);">
                📖 Reporting about: <strong><?= htmlspecialchars($prefillBook[0]['bname']) ?></strong>
            </div>
            <?php endif; ?>
            <?php endif; ?>
            <?php endif; ?>

            <label>Complaint Type</label>
            <?php
            $ctypes = ['Book Content', 'Payment', 'Technical', 'Other'];
            $selectedType = isset($_POST['complaint_type']) ? $_POST['complaint_type'] : $prefillType;
            ?>
            <select name="complaint_type" class="form-control" style="margin-bottom:16px;">
                <?php foreach ($ctypes as $ct): ?>
                <option value="<?= $ct ?>" <?= $selectedType === $ct ? 'selected' : '' ?>><?= $ct ?></option>
                <?php endforeach; ?>
            </select>
            
            <label>Subject</label>
            <?= $form->textBox('subject', array('class' => 'form-control', 'placeholder' => 'Brief subject of your complaint', 'style' => 'margin-bottom:16px;')); ?>
            <div style="color:#ef4444; font-size:12px; margin-top:-12px; margin-bottom:16px;"><?= $validator->error('subject'); ?></div>
            
            <label>Description</label>
            <textarea name="description" class="form-control" rows="5" style="margin-bottom:16px; resize:vertical;" placeholder="Describe your complaint in detail..."><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
            <div style="color:#ef4444; font-size:12px; margin-top:-12px; margin-bottom:16px;"><?= $validator->error('description'); ?></div>
            
            <button type="submit" name="btn_complaint" class="btn" style="width:100%; padding:12px; background:#ef4444; color:#fff; box-shadow:0 0 15px rgba(239,68,68,0.3); display:flex; align-items:center; justify-content:center; gap:8px;">
                <i data-lucide="send" style="width:16px;height:16px;"></i> Submit Complaint
            </button>
        </form>
    </div>
    
    <div>
        <h3 style="margin-top:0; margin-bottom:20px; display:flex; align-items:center; gap:8px;">
            <i data-lucide="list" style="width:20px;height:20px;stroke:var(--accent);"></i> My Complaints
        </h3>
        <?php if ($complaints): ?>
        <div class="glass-card" style="padding:0; overflow:hidden;">
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Filed On</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($complaints as $c):
                    $statusLabels = [1 => 'Open', 2 => 'In Progress', 3 => 'Resolved', 0 => 'Rejected'];
                    $statusStyles = [
                        1 => 'background:rgba(59,130,246,0.1); color:#3b82f6;', 
                        2 => 'background:rgba(245,158,11,0.1); color:#f59e0b;', 
                        3 => 'background:rgba(16,185,129,0.1); color:#10b981;', 
                        0 => 'background:rgba(239,68,68,0.1); color:#ef4444;'
                    ];
                ?>
                    <tr>
                        <td style="vertical-align:top;">
                            <div style="font-weight:600; margin-bottom:6px;"><?= htmlspecialchars($c['subject']) ?></div>
                            <?php if ($c['admin_reply']): ?>
                            <div style="font-size:13px; padding:10px; background:rgba(0,0,0,0.02); border-radius:8px; border-left:3px solid var(--accent); margin-top:10px;">
                                <strong style="color:var(--text-main); display:flex; align-items:center; gap:6px; margin-bottom:4px;">
                                    <i data-lucide="message-square" style="width:14px;height:14px;"></i> Admin Reply:
                                </strong>
                                <span style="color:var(--text-muted);"><?= nl2br(htmlspecialchars($c['admin_reply'])) ?></span>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td style="vertical-align:top;"><span style="font-size:13px; color:var(--text-muted);"><?= htmlspecialchars($c['complaint_type']) ?></span></td>
                        <td style="vertical-align:top;"><span style="padding:4px 8px; border-radius:4px; font-size:12px; font-weight:600; <?= $statusStyles[$c['status']] ?>"><?= $statusLabels[$c['status']] ?></span></td>
                        <td style="vertical-align:top; font-size:13px; color:var(--text-muted);"><?= date('d M Y', strtotime($c['filed_date'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div style="padding:60px 40px; text-align:center; color:var(--text-muted); border:1px dashed var(--border); border-radius:12px; background:var(--card);">
            <i data-lucide="check-circle" style="width:48px;height:48px;margin-bottom:12px;opacity:0.5;stroke:var(--accent);"></i>
            <div style="font-size:16px;font-weight:600;">No Complaints</div>
            <div style="font-size:14px;margin-top:6px;">You haven't submitted any complaints yet.</div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php include("readerFooter.php"); ?>
