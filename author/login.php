<?php 
require('../config/autoload.php'); 
$dao = new DataAccess();
$elements = array("email"=>"","password"=>""); 
$form = new FormAssist($elements,$_POST);
$rules = array('email'=>array('required'=>true), 'password'=>array('required'=>true));
$validator = new FormValidator($rules);

if(isset($_POST['login'])) {
    if($validator->validate($_POST)) {
        $data = array('email'=>$_POST['email'],'password'=>$_POST['password']);
        if($info = $dao->login($data,'authorregistration')) {
            if($info['status'] == 2) {
                $_SESSION['authid'] = $info['authid'];
                $_SESSION['authname'] = $info['username'];
                echo "<script>alert('Welcome back!'); location.replace('dashboard.php');</script>";
                exit();
            } else {
                echo "<script>alert('Your account is not active yet! Please wait for admin approval.');</script>";
            }
        } else {
            echo "<script>alert('Invalid email or password');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Author Login | Ready 2 Read</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #09090b;
            --card: #18181b;
            --accent: #6366f1;
            --accent-glow: rgba(99, 102, 241, 0.4);
            --border: #27272a;
            --text-main: #fafafa;
            --text-muted: #a1a1aa;
            --input-bg: #09090b;
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
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
        }

        .brand { text-align:center; margin-bottom: 40px; }
        .site-name { font-size: 28px; font-weight: 800; letter-spacing: -1px; text-transform: uppercase; background: linear-gradient(to right, #fff, var(--accent)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .site-tagline { font-size: 11px; text-transform: uppercase; letter-spacing: 2px; color: var(--accent); font-weight: 700; margin-top: 5px; }

        .field-group { margin-bottom: 20px; }
        label { display: block; font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 8px; }
        
        .form-control {
            width: 100%; padding: 12px 14px; background: var(--input-bg);
            border: 1px solid var(--border); border-radius: 8px;
            color: var(--text-main); font-size: 14px; transition: border-color 0.2s;
        }
        .form-control:focus { outline: none; border-color: var(--accent); }
        .error-hint { font-size: 12px; color: #ef4444; margin-top: 4px; }

        .btn-primary {
            width: 100%; padding: 14px; background: var(--accent);
            border: none; border-radius: 8px; color: white;
            font-size: 14px; font-weight: 600; cursor: pointer;
            transition: opacity 0.2s; margin-top: 10px;
        }
        .btn-primary:hover { opacity: 0.9; }

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
        <div class="site-tagline">Author Studio</div>
    </div>

    <form method="POST" action="">
        <div class="field-group">
            <label>Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="author@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            <div class="error-hint"><?= $validator->error('email'); ?></div>
        </div>

        <div class="field-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            <div class="error-hint"><?= $validator->error('password'); ?></div>
        </div>

        <button type="submit" name="login" class="btn-primary">Sign In</button>
    </form>

    <div class="footer-link">
        Don't have an author account? <br><a href="authorRegister.php" style="display:inline-block;margin-top:10px;">Apply Now</a>
    </div>
</div>

</body>
</html>