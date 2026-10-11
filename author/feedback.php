<?php
require('../config/autoload.php');
if (!isset($_SESSION['authid'])) { header('location:login.php'); exit(); }

$authid = $_SESSION['authid'];
$dao = new DataAccess();

$msg = "";
if (isset($_POST['btn_send'])) {
    $subject = addslashes(trim($_POST['subject']));
    $message = addslashes(trim($_POST['message']));
    
    if (empty($subject) || empty($message)) {
        $msg = "<div style='color:#ef4444;margin-bottom:15px;font-size:13px;'>All fields are required.</div>";
    } else {
        $data = array(
            'sender_id' => $authid,
            'sender_type' => 'author',
            'subject' => $subject,
            'message' => $message,
            'feedback_date' => date('Y-m-d H:i:s'),
            'status' => 1
        );
        if ($dao->insert($data, 'feedback')) {
            $msg = "<div style='color:#10b981;margin-bottom:15px;font-size:13px;background:rgba(16,185,129,0.1);padding:10px;border-radius:8px;'>Feedback sent successfully to Admin!</div>";
        } else {
            $msg = "<div style='color:#ef4444;margin-bottom:15px;font-size:13px;'>Failed to send. Try again.</div>";
        }
    }
}

include("header.php");
?>

<div style="display:grid; grid-template-columns: 1fr 2fr; gap: 30px;">
    
    <!-- Send Form -->
    <div class="glass-card" style="height:fit-content;">
        <h2 style="font-size:16px;font-weight:600;margin-bottom:20px;">Send Feedback to Admin</h2>
        <?= $msg ?>
        <form action="" method="POST">
            <div class="field-group">
                <label class="input-label">Subject</label>
                <input type="text" name="subject" class="form-control" placeholder="What is this regarding?" required>
            </div>
            <div class="field-group">
                <label class="input-label">Message</label>
                <textarea name="message" class="form-control" rows="6" placeholder="Describe your issue or feedback in detail..." required></textarea>
            </div>
            <button type="submit" name="btn_send" class="btn btn-full">📤 Send Message</button>
        </form>
    </div>

    <!-- History -->
    <div class="glass-card">
        <h2 style="font-size:16px;font-weight:600;margin-bottom:20px;">Feedback History</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $history = $dao->query("SELECT * FROM feedback WHERE sender_id=$authid AND sender_type='author' ORDER BY feedback_date DESC");
                if (!empty($history)):
                    foreach ($history as $h):
                ?>
                <tr>
                    <td style="color:var(--text-muted);font-size:12px;white-space:nowrap;"><?= date('M d, Y', strtotime($h['feedback_date'])) ?></td>
                    <td style="font-weight:500;"><?= htmlspecialchars($h['subject']) ?></td>
                    <td style="color:var(--text-muted);font-size:13px;max-width:250px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        <?= htmlspecialchars($h['message']) ?>
                    </td>
                    <td>
                        <?php if ($h['status'] == 1): ?>
                            <span class="badge badge-paid">Unread</span>
                        <?php elseif ($h['status'] == 2): ?>
                            <span class="badge badge-free">Read</span>
                        <?php else: ?>
                            <span class="badge badge-open">Archived</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="4" style="padding:30px;text-align:center;color:var(--text-muted);">No feedback history.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include("footer.php"); ?>
