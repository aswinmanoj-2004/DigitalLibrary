<?php
require('../config/autoload.php');
include("header.php");
include("dbcon.php");

$authid = (int)($_GET['id'] ?? 0);
$res    = $conn->query("SELECT username, email FROM authorregistration WHERE authid=$authid");
$author = $res->fetch_assoc();
?>

<div class="section">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <div>
            <h2>📚 Books by <?= htmlspecialchars($author['username'] ?? 'Unknown') ?></h2>
            <p style="color:var(--text-muted);font-size:13px;margin-top:4px;"><?= htmlspecialchars($author['email'] ?? '') ?></p>
        </div>
        <a href="AllSellers.php" class="btn btn-secondary">← Back to Sellers</a>
    </div>

    <div class="table-wrap">
        <table>
            <tr>
                <th>Sl</th>
                <th>Book Name</th>
                <th>Category</th>
                <th>Cover</th>
                <th>Type</th>
                <th>Price</th>
                <th>Added Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php
            $sql = "SELECT bid, bname, cid, CoverImage, is_paid, price, AddedDate, status
                    FROM books
                    WHERE AuthorName = (SELECT username FROM authorregistration WHERE authid=$authid)
                    ORDER BY AddedDate DESC";
            $result = $conn->query($sql);
            $i = 1;
            while ($row = $result->fetch_assoc()):
            ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['bname']) ?></td>
                <td style="color:var(--text-muted);"><?= $row['cid'] ?></td>
                <td>
                    <img src="../uploads/<?= htmlspecialchars($row['CoverImage']) ?>"
                         style="width:48px;height:64px;object-fit:cover;border-radius:6px;">
                </td>
                <td>
                    <span class="badge <?= $row['is_paid'] === 'paid' ? 'badge-paid' : 'badge-free' ?>">
                        <?= ucfirst($row['is_paid']) ?>
                    </span>
                </td>
                <td style="color:var(--success);font-weight:600;">₹<?= $row['price'] ?></td>
                <td style="font-size:12px;color:var(--text-muted);"><?= $row['AddedDate'] ?></td>
                <td>
                    <span class="badge <?= $row['status'] == 1 ? 'badge-active' : 'badge-banned' ?>">
                        <?= $row['status'] == 1 ? 'Active' : 'Inactive' ?>
                    </span>
                </td>
                <td>
                    <a href="DisableBook.php?id=<?= $row['bid'] ?>&from=seller&authid=<?= $authid ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Disable this book?')">🚫 Disable</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<?php include("footer.php"); ?>
