<?php
require('../config/autoload.php');
include("header.php");
include("dbcon.php");

$cid = (int)($_GET['id'] ?? 0);
$msg = "";

// Handle form submit
if (isset($_POST['btn_reply'])) {
    $reply    = $conn->real_escape_string(trim($_POST['admin_reply']));
    $status   = (int)$_POST['status'];
    $resolved = ($status == 3) ? ", resolved_date=NOW()" : "";
    $sql      = "UPDATE complaints SET admin_reply='$reply', status=$status $resolved WHERE complaintid=$cid";
    if ($conn->query($sql)) {
        echo "<script>alert('Complaint updated successfully'); window.location.href='Complaints.php';</script>";
        exit();
    } else {
        $msg = "Update failed. Please try again.";
    }
}

$res       = $conn->query("SELECT * FROM complaints WHERE complaintid=$cid");
$complaint = $res->fetch_assoc();

if (!$complaint) {
    echo "<div class='section'><p>Complaint not found. <a href='Complaints.php' class='btn btn-secondary btn-sm'>← Go back</a></p></div>";
    include("footer.php"); exit();
}

$rRes   = $conn->query("SELECT username, email FROM readerregistration WHERE userId=" . $complaint['userId']);
$reader = $rRes->fetch_assoc();

$book = null;
if ($complaint['bid'] > 0) {
    $bRes = $conn->query("SELECT bname FROM books WHERE bid=" . $complaint['bid']);
    $book = $bRes->fetch_assoc();
}

$statusLabels = array(0=>'Rejected', 1=>'Open', 2=>'In Progress', 3=>'Resolved');
$statusBadges = array(0=>'badge-banned', 1=>'badge-open', 2=>'badge-pending', 3=>'badge-resolved');
?>

<!-- Complaint Details -->
<div class="section" style="max-width:780px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2>🔔 Complaint #<?= $cid ?></h2>
        <a href="Complaints.php" class="btn btn-secondary btn-sm">← Back to Complaints</a>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-danger"><?= $msg ?></div>
    <?php endif; ?>

    <!-- Detail rows -->
    <table style="width:100%;border-collapse:collapse;margin-bottom:24px;">
        <?php
        $details = [
            'Complaint ID'  => '#' . $complaint['complaintid'],
            'Reader'        => htmlspecialchars($reader['username'] ?? 'Unknown') . ($reader ? ' <span style="color:var(--text-muted);font-size:12px;">(' . $reader['email'] . ')</span>' : ''),
            'Related Book'  => $book ? htmlspecialchars($book['bname']) : '<em style="color:var(--text-muted);">General (no specific book)</em>',
            'Type'          => htmlspecialchars($complaint['complaint_type']),
            'Subject'       => htmlspecialchars($complaint['subject']),
            'Description'   => '<span style="white-space:pre-wrap;">' . htmlspecialchars($complaint['description']) . '</span>',
            'Filed Date'    => date('d M Y, h:i A', strtotime($complaint['filed_date'])),
            'Current Status'=> '<span class="badge ' . ($statusBadges[$complaint['status']] ?? 'badge-pending') . '">' . ($statusLabels[$complaint['status']] ?? 'Unknown') . '</span>',
        ];
        if ($complaint['resolved_date']) {
            $details['Resolved Date'] = date('d M Y, h:i A', strtotime($complaint['resolved_date']));
        }
        foreach ($details as $label => $value):
        ?>
        <tr>
            <th style="padding:11px 14px;background:rgba(255,255,255,0.03);border-bottom:1px solid var(--border);color:var(--text-muted);font-size:12px;font-weight:500;text-transform:uppercase;letter-spacing:0.5px;width:170px;text-align:left;">
                <?= $label ?>
            </th>
            <td style="padding:11px 14px;border-bottom:1px solid var(--border);color:var(--text-main);">
                <?= $value ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if ($complaint['admin_reply']): ?>
        <tr>
            <th style="padding:11px 14px;background:rgba(255,255,255,0.03);border-bottom:1px solid var(--border);color:var(--text-muted);font-size:12px;font-weight:500;text-transform:uppercase;letter-spacing:0.5px;text-align:left;">
                Previous Reply
            </th>
            <td style="padding:11px 14px;border-bottom:1px solid var(--border);color:var(--text-main);white-space:pre-wrap;background:rgba(16,185,129,0.05);">
                <?= htmlspecialchars($complaint['admin_reply']) ?>
            </td>
        </tr>
        <?php endif; ?>
    </table>

    <!-- Reply Form -->
    <h3 style="margin-bottom:16px;">✍️ Reply &amp; Update Status</h3>
    <form action="" method="POST">
        <label>Admin Reply</label>
        <textarea name="admin_reply" class="form-control" rows="6" required
        ><?= htmlspecialchars($complaint['admin_reply'] ?? '') ?></textarea>

        <label style="margin-top:4px;">Update Status</label>
        <select name="status" class="form-control" style="max-width:260px;">
            <option value="1" <?= $complaint['status']==1?'selected':'' ?>>🔴 Open</option>
            <option value="2" <?= $complaint['status']==2?'selected':'' ?>>🟠 In Progress</option>
            <option value="3" <?= $complaint['status']==3?'selected':'' ?>>🟢 Resolved</option>
            <option value="0" <?= $complaint['status']==0?'selected':'' ?>>⛔ Rejected</option>
        </select>

        <div style="margin-top:20px;display:flex;gap:10px;">
            <button type="submit" name="btn_reply" class="btn btn-primary">✅ Submit Reply</button>
            <a href="Complaints.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include("footer.php"); ?>
