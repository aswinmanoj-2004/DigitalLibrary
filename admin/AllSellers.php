<?php
require('../config/autoload.php');
include("header.php");
include("dbcon.php");
?>

<div class="section">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <h2>👥 All Approved Sellers</h2>
        <a href="viewusers.php" class="btn btn-warning">⏳ Pending Approvals</a>
    </div>
    <div class="table-wrap">
        <table>
            <tr>
                <th>Sl No</th>
                <th>Author ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Action</th>
            </tr>
            <?php
            $result = $conn->query("SELECT authid, username, email, phone FROM authorregistration WHERE status=2 ORDER BY authid DESC");
            $i = 1;
            while ($row = $result->fetch_assoc()):
            ?>
            <tr>
                <td><?= $i++ ?></td>
                <td style="color:var(--text-muted);">#<?= $row['authid'] ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td style="font-size:12.5px;color:var(--text-muted);"><?= htmlspecialchars($row['email']) ?></td>
                <td style="color:var(--text-muted);"><?= $row['phone'] ?></td>
                <td>
                    <a href="SellerBooks.php?id=<?= $row['authid'] ?>" class="btn btn-secondary btn-sm" style="margin-right:6px;">📚 View Books</a>
                    <a href="manage_seller.php?action=revoke&id=<?= $row['authid'] ?>" class="btn btn-danger btn-sm"
                       onclick="return confirm('Revoke this seller\'s approval?')">🚫 Revoke</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<?php include("footer.php"); ?>
