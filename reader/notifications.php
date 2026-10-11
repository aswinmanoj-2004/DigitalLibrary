<?php
require('../config/autoload.php');
if (!isset($_SESSION['reader_id'])) {
    header('location: readerLogin.php'); exit();
}
include("readerHeader.php");

$dao      = new DataAccess();
$readerId = $_SESSION['reader_id'];

$notifications = $dao->query("
    SELECT * FROM notifications
    WHERE (receiver_id='$readerId' OR receiver_id=0)
    AND receiver_type IN ('reader','all')
    ORDER BY sent_date DESC
");

// Mark as read
if ($notifications) {
    $dao->update(['status' => 2], "notifications",
                 "(receiver_id='$readerId' OR receiver_id=0) AND receiver_type IN ('reader','all') AND status=1");
}
?>
<div class="glass-card" style="padding:20px;">
    <?php if ($notifications): foreach ($notifications as $n): $isNew = $n['status'] == 1; ?>
    <div style="padding:24px; border-bottom:1px solid var(--border); display:flex; gap:20px; align-items:flex-start; background:<?= $isNew ? 'rgba(16,185,129,0.05)' : 'transparent' ?>; border-radius:12px; margin-bottom:12px; transition:0.2s;">
        <div style="width:48px; height:48px; border-radius:50%; background:<?= $isNew ? 'rgba(16,185,129,0.1)' : 'rgba(255,255,255,0.05)' ?>; color:<?= $isNew ? 'var(--accent)' : 'var(--text-muted)' ?>; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <i data-lucide="<?= $isNew ? 'bell-ring' : 'bell' ?>" style="width:24px;height:24px;"></i>
        </div>
        <div style="flex:1;">
            <div style="display:flex; justify-content:space-between; margin-bottom:8px; align-items:center;">
                <h4 style="font-size:16px; font-weight:700; margin:0; display:flex; align-items:center; gap:10px;">
                    <?= htmlspecialchars($n['title']) ?> 
                    <?php if($isNew): ?><span class="badge badge-free">NEW</span><?php endif; ?>
                </h4>
                <span style="font-size:12px; color:var(--text-muted); font-weight:600;"><?= date('d M Y, H:i', strtotime($n['sent_date'])) ?></span>
            </div>
            <p style="color:var(--text-muted); font-size:14px; margin:0; line-height:1.6;"><?= nl2br(htmlspecialchars($n['message'])) ?></p>
            <?php if ($n['receiver_id'] == 0): ?>
                <div style="margin-top:12px; font-size:11px; color:var(--accent); font-weight:600; text-transform:uppercase; letter-spacing:1px; display:flex; align-items:center; gap:6px;">
                    <i data-lucide="radio" style="width:14px;height:14px;"></i> Global Broadcast
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; else: ?>
    <div style="padding:60px; text-align:center; color:var(--text-muted); border:1px dashed var(--border); border-radius:12px;">
        <i data-lucide="mail-open" style="width:48px;height:48px;margin-bottom:16px;opacity:0.5;"></i>
        <div style="font-size:16px;font-weight:600;">No notifications yet.</div>
        <div style="font-size:14px;margin-top:6px;">You're all caught up!</div>
    </div>
    <?php endif; ?>
</div>
<?php include("readerFooter.php"); ?>
