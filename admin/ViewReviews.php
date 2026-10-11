<?php
require('../config/autoload.php');
include("header.php");
include("dbcon.php");

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$where  = ($filter === 'visible') ? 'rv.status=1'
        : (($filter === 'hidden') ? 'rv.status=0' : '1');
?>

<div class="section">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <h2>⭐ Book Reviews</h2>
    </div>

    <div class="filter-bar">
        <a href="ViewReviews.php"               class="<?= $filter=='all'?'active':'' ?>">All Reviews</a>
        <a href="ViewReviews.php?filter=visible" class="<?= $filter=='visible'?'active':'' ?>">🟢 Visible</a>
        <a href="ViewReviews.php?filter=hidden"  class="<?= $filter=='hidden'?'active':'' ?>">🔴 Hidden</a>
    </div>

    <div class="table-wrap">
        <table>
            <tr>
                <th>Sl</th>
                <th>Book</th>
                <th>Reader</th>
                <th>Rating</th>
                <th>Review</th>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php
            $sql = "SELECT rv.reviewid, b.bname, r.username,
                           rv.rating, rv.review_text, rv.review_date, rv.status
                    FROM reviews rv
                    LEFT JOIN books b              ON rv.bid    = b.bid
                    LEFT JOIN readerregistration r ON rv.userId = r.userId
                    WHERE $where ORDER BY rv.review_date DESC";
            $result = $conn->query($sql);
            $i = 1;
            while ($row = $result->fetch_assoc()):
                $stars = str_repeat('★', $row['rating']) . str_repeat('☆', 5 - $row['rating']);
            ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['bname'] ?? 'Unknown') ?></td>
                <td><?= htmlspecialchars($row['username'] ?? 'Unknown') ?></td>
                <td style="color:#f59e0b;font-size:15px;letter-spacing:1px;"><?= $stars ?> <span style="font-size:11px;color:var(--text-muted);">(<?= $row['rating'] ?>/5)</span></td>
                <td style="max-width:260px;font-size:12.5px;color:var(--text-muted);">
                    <?= htmlspecialchars(substr($row['review_text'], 0, 100)) ?><?= strlen($row['review_text']) > 100 ? '...' : '' ?>
                </td>
                <td style="font-size:12px;color:var(--text-muted);"><?= date('d M Y', strtotime($row['review_date'])) ?></td>
                <td>
                    <?php if ($row['status'] == 1): ?>
                        <span class="badge badge-active">Visible</span>
                    <?php else: ?>
                        <span class="badge badge-banned">Hidden</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($row['status'] == 1): ?>
                        <a href="HideReview.php?id=<?= $row['reviewid'] ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('Hide this review?')">🙈 Hide</a>
                    <?php else: ?>
                        <a href="ShowReview.php?id=<?= $row['reviewid'] ?>" class="btn btn-success btn-sm">👁️ Show</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<?php include("footer.php"); ?>
