<?php
require('../config/autoload.php');
include("header.php");
include("dbcon.php");
?>

<div class="section">
    <h2> Pending Seller Approvals</h2>
    <p style="color:var(--text-muted);font-size:13px;margin-bottom:18px;">
        Review and approve or reject seller registration requests.
    </p>
    <div class="table-wrap">
        <table>
            <tr>
                <th>Sl No</th>
                <th>Author ID</th>
                <th>Username</th>
                <th>Action</th>
            </tr>
            <?php
            $result = $conn->query("SELECT authid, username FROM authorregistration WHERE status=1 ORDER BY authid DESC");
            $i = 1;
            while ($row = $result->fetch_assoc()):
            ?>
            <tr>
                <td><?= $i++ ?></td>
                <td style="color:var(--text-muted);">#<?= $row['authid'] ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td>
                    <a href="manage_seller.php?action=approve&id=<?= $row['authid'] ?>" class="btn btn-success btn-sm" style="margin-right:6px;">✅ Approve</a>
                    <a href="manage_seller.php?action=reject&id=<?= $row['authid'] ?>"  class="btn btn-danger btn-sm"  onclick="return confirm('Reject this seller?')">❌ Reject</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<div style="margin-top:10px;">
    <a href="AllSellers.php" class="btn btn-secondary">👥 View All Approved Sellers</a>
</div>

<?php include("footer.php"); ?>