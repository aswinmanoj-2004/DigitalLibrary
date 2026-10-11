<?php
require('../config/autoload.php');
if (!isset($_SESSION['authid'])) { header('location:login.php'); exit(); }

$authid = $_SESSION['authid'];
$dao = new DataAccess();

$msg = "";
// Profile update
if (isset($_POST['btn_update'])) {
    $email = addslashes(trim($_POST['email']));
    $phone = addslashes(trim($_POST['phone']));
    
    if (empty($email) || empty($phone)) {
        $msg = "<div style='color:#ef4444;margin-bottom:15px;font-size:13px;'>All fields are required.</div>";
    } else {
        $data = array('email' => $email, 'phone' => $phone);
        if ($dao->update($data, 'authorregistration', "authid=$authid")) {
            $msg = "<div style='color:#10b981;margin-bottom:15px;font-size:13px;background:rgba(16,185,129,0.1);padding:10px;border-radius:8px;'>Profile updated successfully!</div>";
        } else {
            $msg = "<div style='color:#ef4444;margin-bottom:15px;font-size:13px;'>Failed to update.</div>";
        }
    }
}

$user = $dao->query("SELECT * FROM authorregistration WHERE authid=$authid")[0];

include("header.php");
?>

<div style="max-width:600px; margin:0 auto;">
    <div style="text-align:center; margin-bottom:30px;">
        <div style="width:100px;height:100px;background:var(--card);border:2px solid var(--accent);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:32px;font-weight:700;color:var(--accent);margin:0 auto 15px auto;">
            <?= strtoupper(substr($user['username'], 0, 2)) ?>
        </div>
        <h2 style="font-size:24px;font-weight:700;"><?= htmlspecialchars($user['username']) ?></h2>
        <p style="color:var(--text-muted);font-size:14px;margin-top:5px;">Professional Author</p>
    </div>

    <div class="glass-card">
        <h3 style="font-size:16px;font-weight:600;margin-bottom:20px;">Personal Details</h3>
        <?= $msg ?>
        <form action="" method="POST">
            <div class="field-group">
                <label class="input-label">Username</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" readonly style="opacity:0.6;cursor:not-allowed;">
                <p style="font-size:11px;color:var(--text-muted);margin-top:4px;">Username cannot be changed.</p>
            </div>
            
            <div class="field-group">
                <label class="input-label">Email Address</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            
            <div class="field-group">
                <label class="input-label">Phone Number</label>
                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required>
            </div>

            <button type="submit" name="btn_update" class="btn btn-full">Save Changes</button>
        </form>
    </div>
</div>

<?php include("footer.php"); ?>
