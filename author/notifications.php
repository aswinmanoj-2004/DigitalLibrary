<?php
require('../config/autoload.php');
if (!isset($_SESSION['authid'])) {
    header('location: login.php'); exit();
}

$dao    = new DataAccess();
$authId = $_SESSION['authid'];

// Fetch notifications for this author (direct + broadcast)
$notifications = $dao->query("
    SELECT * FROM notifications
    WHERE (receiver_id='$authId' OR receiver_id=0)
    AND receiver_type IN ('author','all')
    ORDER BY sent_date DESC
");

// Mark unread ones as read
if ($notifications) {
    $dao->update(
        ['status' => 2],
        "notifications",
        "(receiver_id='$authId' OR receiver_id=0) AND receiver_type IN ('author','all') AND status=1"
    );
}

include("header.php");
?>
<div style="max-width:860px;">

    <!-- Page intro -->
    <div style="margin-bottom:30px; padding:20px 24px; background:rgba(99,102,241,0.07); border:1px solid rgba(99,102,241,0.2); border-radius:12px; display:flex; align-items:center; gap:16px;">
        <div style="width:44px; height:44px; border-radius:50%; background:var(--accent-soft); color:var(--accent); display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0;">🔔</div>
        <div>
            <div style="font-weight:700; font-size:15px; margin-bottom:2px;">Admin Notifications</div>
            <div style="font-size:13px; color:var(--text-muted);">Messages and announcements sent to you by the admin team.</div>
        </div>
    </div>

    <?php if ($notifications): ?>
        <?php foreach ($notifications as $n):
            $isNew      = ($n['status'] == 1);
            $isBroadcast = ($n['receiver_id'] == 0);
        ?>
        <div style="
            padding:24px; border-radius:14px; margin-bottom:14px;
            background:<?= $isNew ? 'rgba(99,102,241,0.07)' : 'var(--card)' ?>;
            border:1px solid <?= $isNew ? 'rgba(99,102,241,0.3)' : 'var(--border)' ?>;
            display:flex; gap:18px; align-items:flex-start;
            transition:border-color 0.2s;
        ">
            <!-- Icon -->
            <div style="
                width:46px; height:46px; border-radius:50%; flex-shrink:0;
                background:<?= $isNew ? 'var(--accent-soft)' : 'rgba(255,255,255,0.04)' ?>;
                color:<?= $isNew ? 'var(--accent)' : 'var(--text-muted)' ?>;
                display:flex; align-items:center; justify-content:center; font-size:20px;
            ">
                <?= $isBroadcast ? '📢' : ($isNew ? '🔔' : '🔕') ?>
            </div>

            <!-- Content -->
            <div style="flex:1; min-width:0;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:8px; margin-bottom:10px;">
                    <h4 style="margin:0; font-size:16px; font-weight:700; display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <?= htmlspecialchars($n['title']) ?>
                        <?php if ($isNew): ?>
                        <span style="background:rgba(99,102,241,0.15); color:#818cf8; border:1px solid rgba(99,102,241,0.3); font-size:10px; font-weight:700; padding:2px 8px; border-radius:20px; letter-spacing:1px; text-transform:uppercase;">NEW</span>
                        <?php endif; ?>
                        <?php if ($isBroadcast): ?>
                        <span style="background:rgba(245,158,11,0.1); color:#f59e0b; border:1px solid rgba(245,158,11,0.25); font-size:10px; font-weight:700; padding:2px 8px; border-radius:20px; letter-spacing:1px; text-transform:uppercase;">Broadcast</span>
                        <?php endif; ?>
                    </h4>
                    <span style="font-size:12px; color:var(--text-muted); white-space:nowrap;">
                        <?= date('d M Y, h:i A', strtotime($n['sent_date'])) ?>
                    </span>
                </div>
                <p style="margin:0; color:var(--text-muted); font-size:14px; line-height:1.7;">
                    <?= nl2br(htmlspecialchars($n['message'])) ?>
                </p>
            </div>
        </div>
        <?php endforeach; ?>

    <?php else: ?>
        <div style="padding:80px 40px; text-align:center; background:var(--card); border:1px dashed var(--border); border-radius:14px;">
            <div style="font-size:40px; margin-bottom:16px;">📭</div>
            <div style="font-size:17px; font-weight:600; margin-bottom:8px;">No Notifications Yet</div>
            <div style="font-size:14px; color:var(--text-muted);">You're all caught up! Admin messages will appear here.</div>
        </div>
    <?php endif; ?>

</div>

<?php include("footer.php"); ?>
