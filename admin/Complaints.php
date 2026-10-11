<?php
require('../config/autoload.php');
include("header.php");
include("dbcon.php");

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$statusLabels = array(
    0 => array('label'=>'Rejected',    'class'=>'badge-banned'),
    1 => array('label'=>'Open',        'class'=>'badge-open'),
    2 => array('label'=>'In Progress', 'class'=>'badge-pending'),
    3 => array('label'=>'Resolved',    'class'=>'badge-resolved'),
);
?>

<div class="section">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <h2>🔔 Complaints Management</h2>
    </div>

    <div class="filter-bar">
        <a href="Complaints.php"             class="<?= $filter=='all'?'active':'' ?>">All</a>
        <a href="Complaints.php?filter=1"    class="<?= $filter=='1'?'active':'' ?>">🔴 Open</a>
        <a href="Complaints.php?filter=2"    class="<?= $filter=='2'?'active':'' ?>">🟠 In Progress</a>
        <a href="Complaints.php?filter=3"    class="<?= $filter=='3'?'active':'' ?>">🟢 Resolved</a>
        <a href="Complaints.php?filter=0"    class="<?= $filter=='0'?'active':'' ?>">⛔ Rejected</a>
    </div>

    <div class="table-wrap">
        <table>
            <tr>
                <th>Sl</th>
                <th>ID</th>
                <th>Reader</th>
                <th>Book</th>
                <th>Type</th>
                <th>Subject</th>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php
            $where  = ($filter !== 'all') ? "c.status=" . (int)$filter : "1";
            $sql    = "SELECT c.complaintid, r.username AS reader, b.bname AS book,
                              c.complaint_type, c.subject, c.filed_date, c.status
                       FROM complaints c
                       LEFT JOIN readerregistration r ON c.userId = r.userId
                       LEFT JOIN books b ON c.bid = b.bid AND c.bid > 0
                       WHERE $where ORDER BY c.filed_date DESC";
            $result = $conn->query($sql);
            $i = 1;
            while ($row = $result->fetch_assoc()):
                $s = $statusLabels[$row['status']] ?? array('label'=>'Unknown','class'=>'badge-pending');
            ?>
            <tr>
                <td><?= $i++ ?></td>
                <td style="color:var(--text-muted);">#<?= $row['complaintid'] ?></td>
                <td><?= htmlspecialchars($row['reader'] ?? 'Unknown') ?></td>
                <td style="color:var(--text-muted);font-size:12.5px;"><?= $row['book'] ? htmlspecialchars($row['book']) : 'General' ?></td>
                <td style="font-size:12px;"><?= htmlspecialchars($row['complaint_type']) ?></td>
                <td><?= htmlspecialchars($row['subject']) ?></td>
                <td style="color:var(--text-muted);font-size:12px;"><?= date('d M Y', strtotime($row['filed_date'])) ?></td>
                <td><span class="badge <?= $s['class'] ?>"><?= $s['label'] ?></span></td>
                <td>
                    <a href="ReplyComplaint.php?id=<?= $row['complaintid'] ?>" class="btn btn-primary btn-sm">Reply / Resolve</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<?php include("footer.php"); ?>
