<?php
require('../config/autoload.php');

$dao = new DataAccess();
$msg = "";

if (isset($_POST['btn_login'])) {
    $email = addslashes($_POST['email']);
    $password = addslashes($_POST['password']);

    $result = $dao->query("SELECT * FROM readerregistration WHERE email='$email' AND password='$password'");
    if ($result && count($result) > 0) {
        if ($result[0]['status'] == 1 || $result[0]['status'] == 2) {
            $_SESSION['reader_id']   = $result[0]['userId'];
            $_SESSION['reader_name'] = $result[0]['username'];
            echo "<script>alert('Welcome back, Reader!'); location.replace('readerHome.php');</script>";
            exit();
        } else {
            $msg = "Your account is disabled.";
        }
    } else {
        $msg = "Invalid email or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reader Login | Ready 2 Read</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f8fafc;
            --card: #ffffff;
            --accent: #7c3aed;
            --accent-glow: rgba(124, 58, 237, 0.08);
            --border: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --input-bg: #ffffff;
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter', sans-serif; }

        body {
            background-color: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-container {
            width: 100%;
            max-width: 420px;
            padding: 40px;
            background: var(--card);
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: 0 10px 30px rgba(9, 9, 11, 0.05);
        }

        .brand { text-align:center; margin-bottom: 40px; }
        .site-name { font-size: 28px; font-weight: 800; letter-spacing: -1px; text-transform: uppercase; background: linear-gradient(to right, var(--text-main), var(--accent)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .site-tagline { font-size: 11px; text-transform: uppercase; letter-spacing: 2px; color: var(--accent); font-weight: 700; margin-top: 5px; }

        .field-group { margin-bottom: 20px; }
        label { display: block; font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 8px; }
        
        .form-control {
            width: 100%; padding: 12px 14px; background: var(--input-bg);
            border: 1px solid var(--border); border-radius: 8px;
            color: var(--text-main); font-size: 14px; transition: 0.2s;
        }
        .form-control:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-glow); }

        .btn-primary {
            width: 100%; padding: 14px; background: var(--accent);
            border: none; border-radius: 8px; color: white;
            font-size: 14px; font-weight: 600; cursor: pointer;
            transition: all 0.2s; margin-top: 10px;
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.25);
        }
        .btn-primary:hover { opacity: 0.95; box-shadow: 0 6px 16px rgba(124, 58, 237, 0.35); }

        .footer-link {
            text-align: center; margin-top: 25px; font-size: 13px; color: var(--text-muted);
        }
        .footer-link a { color: var(--accent); text-decoration: none; font-weight: 600; }
        .footer-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="brand">
        <img src="../logo.png" alt="Ready 2 Read" style="height: 60px; object-fit: contain; margin-bottom: 8px;">
        <div class="site-tagline">Reader Library</div>
    </div>

    <?php if ($msg != ""): ?>
        <div style="color:#ef4444;font-size:13px;margin-bottom:16px;padding:10px 14px;background:rgba(239,68,68,0.08);border-radius:8px;text-align:center;"><?= $msg ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="field-group">
            <label>Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="reader@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>

        <div class="field-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>

        <button type="submit" name="btn_login" class="btn-primary">Login to Library</button>
    </form>

    <div class="footer-link">
        Don't have an account? <br><a href="readerRegister.php" style="display:inline-block;margin-top:10px;">Register Now</a>
    </div>
</div>

</body>
</html>
