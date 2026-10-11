<?php
require('../config/autoload.php');
if (!isset($_SESSION['authid'])) {
    header('location:login.php');
    exit();
}

$authid = $_SESSION['authid'];
$authorName = $_SESSION['authname'];
$dao = new DataAccess();

include("header.php");

$safeAuthorName = addslashes($authorName);
$complaints = $dao->query("
    SELECT c.*, b.bname, r.username AS reader_name, r.email AS reader_email
    FROM complaints c
    JOIN books b ON c.bid = b.bid
    JOIN readerregistration r ON c.userId = r.userId
    WHERE b.AuthorName = '$safeAuthorName'
    ORDER BY c.filed_date DESC
");
?>

<div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:24px;">
    <div>
        <h2 style="font-size:18px; font-weight:600;">Reader Complaints</h2>
        <p style="color:var(--text-muted); font-size:13px; margin-top:4px;">View issues reported by readers about your published books.</p>
    </div>
</div>

<div class="glass-card" style="padding:0; overflow:hidden;">
    <?php if (!empty($complaints)): ?>
    <table style="width:100%; border-collapse:collapse; font-size:13px;">
        <thead>
            <tr style="border-bottom:1px solid var(--border);">
                <th style="padding:16px; text-align:left; color:var(--text-muted); font-weight:500;">Book Info</th>
                <th style="padding:16px; text-align:left; color:var(--text-muted); font-weight:500;">Reader Details</th>
                <th style="padding:16px; text-align:left; color:var(--text-muted); font-weight:500;">Complaint Details</th>
                <th style="padding:16px; text-align:left; color:var(--text-muted); font-weight:500;">Status</th>
                <th style="padding:16px; text-align:left; color:var(--text-muted); font-weight:500;">Filed On</th>
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
            <tr style="border-bottom:1px solid var(--border);">
                <td style="padding:16px; vertical-align:top; font-weight:600; color:var(--text-main);">
                    <?= htmlspecialchars($c['bname']) ?>
                </td>
                <td style="padding:16px; vertical-align:top;">
                    <div style="font-weight:500; color:var(--text-main);"><?= htmlspecialchars($c['reader_name']) ?></div>
                    <div style="font-size:11px; color:var(--text-muted); margin-top:2px;"><?= htmlspecialchars($c['reader_email']) ?></div>
                </td>
                <td style="padding:16px; vertical-align:top; max-width:400px;">
                    <div style="font-size:11px; text-transform:uppercase; color:var(--accent); font-weight:600; margin-bottom:4px;">
                        <?= htmlspecialchars($c['complaint_type']) ?>
                    </div>
                    <div style="font-weight:600; font-size:14px; margin-bottom:6px; color:var(--text-main);">
                        <?= htmlspecialchars($c['subject']) ?>
                    </div>
                    <div style="color:var(--text-muted); line-height:1.5; font-size:13px; white-space:pre-wrap;"><?= htmlspecialchars($c['description']) ?></div>
                    
                    <?php if ($c['admin_reply']): ?>
                    <div style="font-size:12.5px; padding:12px; background:rgba(255,255,255,0.03); border-radius:8px; border-left:3px solid var(--accent); margin-top:12px;">
                        <strong style="color:var(--text-main); display:flex; align-items:center; gap:6px; margin-bottom:4px;">
                            <i data-lucide="message-square" style="width:13px;height:13px;"></i> Admin Reply:
                        </strong>
                        <span style="color:var(--text-muted);"><?= nl2br(htmlspecialchars($c['admin_reply'])) ?></span>
                    </div>
                    <?php endif; ?>
                </td>
                <td style="padding:16px; vertical-align:top;">
                    <span class="badge" style="padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; <?= $statusStyles[$c['status']] ?? 'background:rgba(255,255,255,0.05); color:var(--text-muted);' ?>">
                        <?= $statusLabels[$c['status']] ?? 'Unknown' ?>
                    </span>
                </td>
                <td style="padding:16px; vertical-align:top; color:var(--text-muted); font-size:12px; white-space:nowrap;">
                    <?= date('d M Y, h:i A', strtotime($c['filed_date'])) ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div style="padding:80px 40px; text-align:center; color:var(--text-muted);">
        <i data-lucide="check-circle" style="width:48px;height:48px;margin-bottom:12px;opacity:0.5;stroke:var(--accent);"></i>
        <div style="font-size:16px;font-weight:600;color:var(--text-main);">All Clear!</div>
        <div style="font-size:14px;margin-top:6px;">No reader complaints have been filed for your books.</div>
    </div>
    <?php endif; ?>
</div>

<?php include("footer.php"); ?>
