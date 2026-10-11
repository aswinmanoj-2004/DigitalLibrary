<?php 
require('../config/autoload.php'); 

$dao = new DataAccess();
$elements = array("username"=>"","password"=>"","email"=>"","phone"=>"");
$form = new FormAssist($elements,$_POST);

$labels = array('username'=>"Username","password"=>"Password","phone"=>"Phone Number",'email'=>"Email Address");
$rules = array(
    "username"=>array("required"=>true,"minlength"=>3,"maxlength"=>30,"alphaonly"=>true),
    "password"=>array("required"=>true,"minlength"=>3,"maxlength"=>30),
    "email"=>array("required"=>true,"minlength"=>3,"maxlength"=>30),
    "phone"=>array("required"=>true,"minlength"=>10,"maxlength"=>15,"integeronly"=>true)
);
    
$validator = new FormValidator($rules,$labels);
$msg = "";

if(isset($_POST["btn_insert"])) {
    if($validator->validate($_POST)) {
        $data = array(
            'username'=>$_POST['username'],
            'password'=>$_POST['password'],
            'email'=>$_POST['email'],
            'phone'=>$_POST['phone'],
            'status'=>1
        );
        if($dao->insert($data,"readerregistration")) {
            echo "<script>alert('Registration successful! You can now login.'); location.replace('readerLogin.php');</script>";
            exit();
        } else {
            $msg = "Registration failed!";
        }
    }
} 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reader Registration | Ready 2 Read</title>
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
            padding: 40px 20px;
        }

        .auth-container {
            width: 100%;
            max-width: 450px;
            padding: 40px;
            background: var(--card);
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: 0 10px 30px rgba(9, 9, 11, 0.05);
        }

        .brand { text-align:center; margin-bottom: 30px; }
        .site-name { font-size: 28px; font-weight: 800; letter-spacing: -1px; text-transform: uppercase; background: linear-gradient(to right, var(--text-main), var(--accent)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .site-tagline { font-size: 11px; text-transform: uppercase; letter-spacing: 2px; color: var(--accent); font-weight: 700; margin-top: 5px; }

        .field-group { margin-bottom: 18px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        label { display: block; font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 8px; }
        
        .form-control {
            width: 100%; padding: 12px 14px; background: var(--input-bg);
            border: 1px solid var(--border); border-radius: 8px;
            color: var(--text-main); font-size: 14px; transition: 0.2s;
        }
        .form-control:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-glow); }
        .error-hint { font-size: 12px; color: #ef4444; margin-top: 4px; }

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
        <div class="site-tagline">Reader Registration</div>
    </div>

    <?php if ($msg != ""): ?>
        <div style="color:#ef4444;font-size:13px;margin-bottom:16px;padding:10px 14px;background:rgba(239,68,68,0.08);border-radius:8px;text-align:center;"><?= $msg ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        
        <div class="field-group">
            <label>Username</label>
            <?= $form->textBox('username', array('class'=>'form-control', 'placeholder'=>'username')); ?>
            <div class="error-hint"><?= $validator->error('username'); ?></div>
        </div>

        <div class="field-group">
            <label>Email Address</label>
            <?= $form->textBox('email', array('class'=>'form-control', 'placeholder'=>'reader@example.com')); ?>
            <div class="error-hint"><?= $validator->error('email'); ?></div>
        </div>

        <div class="form-row">
            <div class="field-group">
                <label>Password</label>
                <?= $form->textBox('password', array('class'=>'form-control', 'type'=>'password','placeholder'=>'******')); ?>
                <div class="error-hint"><?= $validator->error('password'); ?></div>
            </div>

            <div class="field-group">
                <label>Phone Number</label>
                <?= $form->textBox('phone', array('class'=>'form-control', 'placeholder'=>'10 digits')); ?>
                <div class="error-hint"><?= $validator->error('phone'); ?></div>
            </div>
        </div>

        <button type="submit" name="btn_insert" class="btn-primary">Register as Reader</button>
    </form>

    <div class="footer-link">
        Already have an account? <a href="readerLogin.php">Sign in here</a>
    </div>
</div>

</body>
</html>
