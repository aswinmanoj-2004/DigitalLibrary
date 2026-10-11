<?php
require('../config/autoload.php');
include("header.php");
include("dbcon.php");

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$where  = ($filter !== 'all') ? "sender_type='" . $conn->real_escape_string($filter) . "'" : "1";
?>

<div class="section">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <h2>💬 Feedback</h2>
    </div>

    <div class="filter-bar">
        <a href="ViewFeedback.php"               class="<?= $filter=='all'?'active':'' ?>">All</a>
        <a href="ViewFeedback.php?filter=reader" class="<?= $filter=='reader'?'active':'' ?>">👤 From Readers</a>
        <a href="ViewFeedback.php?filter=author" class="<?= $filter=='author'?'active':'' ?>">✍️ From Authors</a>
    </div>

    <div class="table-wrap">
        <table>
            <tr>
                <th>Sl</th>
                <th>Sender</th>
                <th>Type</th>
                <th>Subject</th>
                <th>Message</th>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php
            $sql    = "SELECT f.feedid, f.sender_id, f.sender_type, f.subject,
                              f.message, f.feedback_date, f.status
                       FROM feedback f WHERE $where ORDER BY f.feedback_date DESC";
            $result = $conn->query($sql);
            $i = 1;
            while ($row = $result->fetch_assoc()):
                if ($row['sender_type'] == 'reader') {
                    $nRes  = $conn->query("SELECT username FROM readerregistration WHERE userId=" . $row['sender_id']);
                    $name  = $nRes->fetch_assoc()['username'] ?? 'Unknown';
                    $badge = 'badge-reader';
                } else {
                    $nRes  = $conn->query("SELECT username FROM authorregistration WHERE authid=" . $row['sender_id']);
                    $name  = $nRes->fetch_assoc()['username'] ?? 'Unknown';
                    $badge = 'badge-author';
                }
            ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($name) ?></td>
                <td><span class="s-badge <?= $badge ?>"><?= ucfirst($row['sender_type']) ?></span></td>
                <td><?= htmlspecialchars($row['subject']) ?></td>
                <td style="max-width:280px;font-size:12.5px;color:var(--text-muted);">
                    <?= htmlspecialchars(substr($row['message'], 0, 120)) ?><?= strlen($row['message']) > 120 ? '...' : '' ?>
                </td>
                <td style="font-size:12px;color:var(--text-muted);"><?= date('d M Y', strtotime($row['feedback_date'])) ?></td>
                <td>
                    <?php if ($row['status'] == 1): ?>
                        <span class="badge badge-unread">Unread</span>
                    <?php elseif ($row['status'] == 2): ?>
                        <span class="badge badge-read">Read</span>
                    <?php else: ?>
                        <span class="badge badge-pending">Archived</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($row['status'] == 1): ?>
                        <a href="MarkFeedback.php?id=<?= $row['feedid'] ?>" class="btn btn-success btn-sm">✓ Mark Read</a>
                    <?php else: ?>
                        <span style="color:var(--text-muted);font-size:12px;">✓ Done</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<?php include("footer.php"); ?>
