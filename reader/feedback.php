<?php
require('../config/autoload.php');
if (!isset($_SESSION['reader_id'])) {
    header('location: readerLogin.php'); exit();
}

$dao      = new DataAccess();
$readerId = $_SESSION['reader_id'];

$elements = array("subject" => "", "message" => "");
$form     = new FormAssist($elements, $_POST);

$labels = array("subject" => "Subject", "message" => "Message");
$rules  = array(
    "subject" => array("required" => true, "minlength" => 5,  "maxlength" => 100),
    "message" => array("required" => true, "minlength" => 10, "maxlength" => 1000),
);
$validator = new FormValidator($rules, $labels);

$msg = "";

if (isset($_POST['btn_feedback'])) {
    if ($validator->validate($_POST)) {
        $data = [
            'sender_id'     => $readerId,
            'sender_type'   => 'reader',
            'subject'       => $_POST['subject'],
            'message'       => $_POST['message'],
            'feedback_date' => date('Y-m-d H:i:s'),
            'status'        => 1,
        ];
        if ($dao->insert($data, "feedback")) {
            echo "<script>alert('Feedback submitted successfully! Thank you.');</script>";
            header("location: feedback.php");
            exit();
        } else {
            $msg = "Failed to submit feedback.";
        }
    }
}

include("readerHeader.php");

// My past feedback
$myFeedback = $dao->query("SELECT * FROM feedback WHERE sender_id='$readerId' AND sender_type='reader' ORDER BY feedback_date DESC");
?>
<div style="display:grid; grid-template-columns:1fr 2fr; gap:40px; align-items:start;">
    <div class="glass-card">
        <h3 style="margin-top:0; margin-bottom:20px; display:flex; align-items:center; gap:8px;">
            <i data-lucide="message-square" style="width:20px;height:20px;stroke:var(--accent);"></i> Send Feedback
        </h3>
        <?php if ($msg): ?><div style="padding:12px; background:rgba(239,68,68,0.1); color:#ef4444; border-radius:8px; margin-bottom:16px;"><?= $msg ?></div><?php endif; ?>
        <form method="POST">
            <label>Subject</label>
            <?= $form->textBox('subject', array('class' => 'form-control', 'placeholder' => 'Feedback subject', 'style' => 'margin-bottom:16px;')); ?>
            <div style="color:#ef4444; font-size:12px; margin-top:-12px; margin-bottom:16px;"><?= $validator->error('subject'); ?></div>
            
            <label>Message</label>
            <textarea name="message" class="form-control" rows="6" style="margin-bottom:16px; resize:vertical;" placeholder="Your message to admin..."><?= isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '' ?></textarea>
            <div style="color:#ef4444; font-size:12px; margin-top:-12px; margin-bottom:16px;"><?= $validator->error('message'); ?></div>
            
            <button type="submit" name="btn_feedback" class="btn" style="width:100%; padding:12px; display:flex; align-items:center; justify-content:center; gap:8px;">
                <i data-lucide="send" style="width:16px;height:16px;"></i> Send Feedback
            </button>
        </form>
    </div>

    <div>
        <h3 style="margin-top:0; margin-bottom:20px; display:flex; align-items:center; gap:8px;">
            <i data-lucide="history" style="width:20px;height:20px;stroke:var(--text-muted);"></i> My Past Feedback
        </h3>
        <?php if ($myFeedback): foreach ($myFeedback as $f): 
            $statusMap = [1 => 'Sent', 2 => 'Reviewed by Admin', 0 => 'Archived'];
            $colorMap  = [1 => '#94a3b8', 2 => '#10b981', 0 => '#f59e0b'];
        ?>
        <div class="glass-card" style="margin-bottom:16px; padding:24px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:12px; align-items:flex-start;">
                <h4 style="margin:0; font-size:17px; font-weight:600;"><?= htmlspecialchars($f['subject']) ?></h4>
                <div style="text-align:right;">
                    <div style="font-size:12px; font-weight:600; color:<?= $colorMap[$f['status']] ?>; margin-bottom:4px;"><?= $statusMap[$f['status']] ?></div>
                    <div style="font-size:12px; color:var(--text-muted);"><?= date('d M Y', strtotime($f['feedback_date'])) ?></div>
                </div>
            </div>
            <p style="color:var(--text-muted); margin:0; line-height:1.6; font-size:14px;">
                <?= nl2br(htmlspecialchars($f['message'])) ?>
            </p>
        </div>
        <?php endforeach; else: ?>
        <div style="padding:60px 40px; text-align:center; color:var(--text-muted); border:1px dashed var(--border); border-radius:12px; background:var(--card);">
            <i data-lucide="message-circle" style="width:48px;height:48px;margin-bottom:12px;opacity:0.5;stroke:var(--accent);"></i>
            <div style="font-size:16px;font-weight:600;">No Feedback Yet</div>
            <div style="font-size:14px;margin-top:6px;">We'd love to hear from you!</div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include("readerFooter.php"); ?>
