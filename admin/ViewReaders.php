<?php
require('../config/autoload.php');
include("header.php");
include("dbcon.php");
?>

<div class="section">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <h2>👤 All Readers</h2>
    </div>
    <div class="table-wrap">
        <table>
            <tr>
                <th>Sl No</th>
                <th>User ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php
            $result = $conn->query(
                "SELECT userId, username, email, phone, status FROM readerregistration ORDER BY userId DESC"
            );
            $i = 1;
            while ($row = $result->fetch_assoc()):
            ?>
            <tr>
                <td><?= $i++ ?></td>
                <td style="color:var(--text-muted);">#<?= $row['userId'] ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td style="font-size:12.5px;color:var(--text-muted);"><?= htmlspecialchars($row['email']) ?></td>
                <td style="color:var(--text-muted);"><?= $row['phone'] ?></td>
                <td>
                    <?php if ($row['status'] == 1): ?>
                        <span class="badge badge-active">Active</span>
                    <?php else: ?>
                        <span class="badge badge-banned">Banned</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($row['status'] == 1): ?>
                        <a href="BanReader.php?id=<?= $row['userId'] ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('Ban this reader?')">🚫 Ban</a>
                    <?php else: ?>
                        <a href="UnbanReader.php?id=<?= $row['userId'] ?>" class="btn btn-success btn-sm">✅ Unban</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<?php include("footer.php"); ?>
