<?php
require('../config/autoload.php');
include("header.php");
include("dbcon.php");

// --- Count queries ---
$totalBooks      = $conn->query("SELECT COUNT(*) as cnt FROM books WHERE status=1")->fetch_assoc()['cnt'];
$totalCategories = $conn->query("SELECT COUNT(*) as cnt FROM category WHERE status=1")->fetch_assoc()['cnt'];
$totalReaders    = $conn->query("SELECT COUNT(*) as cnt FROM readerregistration WHERE status=1")->fetch_assoc()['cnt'];
$totalSellers    = $conn->query("SELECT COUNT(*) as cnt FROM authorregistration WHERE status=2")->fetch_assoc()['cnt'];
$pendingSellers  = $conn->query("SELECT COUNT(*) as cnt FROM authorregistration WHERE status=1")->fetch_assoc()['cnt'];
$openComplaints  = $conn->query("SELECT COUNT(*) as cnt FROM complaints WHERE status=1")->fetch_assoc()['cnt'];
$totalPurchases  = $conn->query("SELECT COUNT(*) as cnt FROM purchases")->fetch_assoc()['cnt'];
$unreadFeedback  = $conn->query("SELECT COUNT(*) as cnt FROM feedback WHERE status=1")->fetch_assoc()['cnt'];

// --- Recent 5 open complaints ---
$recentComplaints = $conn->query(
    "SELECT c.complaintid, r.username, c.subject, c.complaint_type, c.filed_date
     FROM complaints c
     LEFT JOIN readerregistration r ON c.userId = r.userId
     WHERE c.status=1 ORDER BY c.filed_date DESC LIMIT 5"
);

// --- Pending sellers ---
$pendingSellersList = $conn->query(
    "SELECT authid, username, email FROM authorregistration WHERE status=1 ORDER BY authid DESC LIMIT 5"
);
?>

<!-- ── Stat Cards ── -->
<div class="stat-grid">
    <div class="stat-card sc-blue">
        <div class="sc-icon"><i data-lucide="book-open" style="width:20px;height:20px;stroke:currentColor;"></i></div>
        <div class="sc-value"><?= $totalBooks ?></div>
        <div class="sc-label">Total Books</div>
        <a href="Books.php" class="sc-link">Manage &rarr;</a>
    </div>
    <div class="stat-card sc-green">
        <div class="sc-icon"><i data-lucide="layout-grid" style="width:20px;height:20px;stroke:currentColor;"></i></div>
        <div class="sc-value"><?= $totalCategories ?></div>
        <div class="sc-label">Categories</div>
        <a href="Category.php" class="sc-link">Manage &rarr;</a>
    </div>
    <div class="stat-card sc-blue">
        <div class="sc-icon"><i data-lucide="user" style="width:20px;height:20px;stroke:currentColor;"></i></div>
        <div class="sc-value"><?= $totalReaders ?></div>
        <div class="sc-label">Registered Readers</div>
        <a href="ViewReaders.php" class="sc-link">View &rarr;</a>
    </div>
    <div class="stat-card sc-green">
        <div class="sc-icon"><i data-lucide="users" style="width:20px;height:20px;stroke:currentColor;"></i></div>
        <div class="sc-value"><?= $totalSellers ?></div>
        <div class="sc-label">Approved Sellers</div>
        <a href="AllSellers.php" class="sc-link">View &rarr;</a>
    </div>
    <div class="stat-card sc-amber">
        <div class="sc-icon"><i data-lucide="clock" style="width:20px;height:20px;stroke:currentColor;"></i></div>
        <div class="sc-value"><?= $pendingSellers ?></div>
        <div class="sc-label">Pending Approvals</div>
        <a href="viewusers.php" class="sc-link">Approve &rarr;</a>
    </div>
    <div class="stat-card sc-red">
        <div class="sc-icon"><i data-lucide="alert-circle" style="width:20px;height:20px;stroke:currentColor;"></i></div>
        <div class="sc-value"><?= $openComplaints ?></div>
        <div class="sc-label">Open Complaints</div>
        <a href="Complaints.php" class="sc-link">Resolve &rarr;</a>
    </div>
    <div class="stat-card sc-blue">
        <div class="sc-icon"><i data-lucide="shopping-cart" style="width:20px;height:20px;stroke:currentColor;"></i></div>
        <div class="sc-value"><?= $totalPurchases ?></div>
        <div class="sc-label">Total Purchases</div>
        <a href="ViewPurchases.php" class="sc-link">View &rarr;</a>
    </div>
    <div class="stat-card sc-amber">
        <div class="sc-icon"><i data-lucide="message-square" style="width:20px;height:20px;stroke:currentColor;"></i></div>
        <div class="sc-value"><?= $unreadFeedback ?></div>
        <div class="sc-label">Unread Feedback</div>
        <a href="ViewFeedback.php" class="sc-link">View &rarr;</a>
    </div>
</div>

<!-- ── Panels ── -->
<div class="panels">

    <!-- Recent Open Complaints -->
    <div class="section panel">
        <h3 style="display:flex;align-items:center;gap:8px;"><i data-lucide="alert-triangle" style="width:18px;height:18px;stroke:#ef4444;"></i> Recent Open Complaints</h3>
        <div class="table-wrap">
            <table>
                <tr>
                    <th>Reader</th>
                    <th>Subject</th>
                    <th>Type</th>
                    <th>Date</th>
                </tr>
                <?php while ($row = $recentComplaints->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['username'] ?? 'Unknown') ?></td>
                    <td>
                        <a href="ReplyComplaint.php?id=<?= $row['complaintid'] ?>"
                           style="color:var(--accent); text-decoration:none;">
                            <?= htmlspecialchars($row['subject']) ?>
                        </a>
                    </td>
                    <td><span class="badge badge-open"><?= $row['complaint_type'] ?></span></td>
                    <td style="color:var(--text-muted); font-size:12px;"><?= date('d M', strtotime($row['filed_date'])) ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
        <div style="margin-top:14px;">
            <a href="Complaints.php" class="btn btn-secondary btn-sm">View all complaints →</a>
        </div>
    </div>

    <!-- Pending Sellers -->
    <div class="section panel">
        <h3 style="display:flex;align-items:center;gap:8px;"><i data-lucide="user-check" style="width:18px;height:18px;stroke:#f59e0b;"></i> Pending Seller Approvals</h3>
        <div class="table-wrap">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = $pendingSellersList->fetch_assoc()): ?>
                <tr>
                    <td style="color:var(--text-muted);">#<?= $row['authid'] ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td style="font-size:12px; color:var(--text-muted);"><?= htmlspecialchars($row['email']) ?></td>
                    <td>
                        <a href="manage_seller.php?action=approve&id=<?= $row['authid'] ?>" class="btn btn-success btn-sm" style="margin-right:6px;">Approve</a>
                        <a href="manage_seller.php?action=reject&id=<?= $row['authid'] ?>" class="btn btn-danger btn-sm">Reject</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
        <div style="margin-top:14px;">
            <a href="viewusers.php" class="btn btn-secondary btn-sm">View all pending →</a>
        </div>
    </div>

</div>

<?php include("footer.php"); ?>
