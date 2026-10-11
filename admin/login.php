<?php
require('../config/autoload.php');
include("dbcon.php");

// If already logged in, redirect to Dashboard.php
if (isset($_SESSION['admin_username']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: Dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
        exit();
    }

    // Check database first
    $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ? AND password = ? AND status = 1");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_id'] = $admin['adminid'];
        echo json_encode(['success' => true]);
        exit();
    } else {
        // Fallback for mock credentials (admin / 12345678)
        if ($username === 'admin' && $password === '12345678') {
            $_SESSION['admin_username'] = 'admin';
            $_SESSION['admin_id'] = 1;
            echo json_encode(['success' => true]);
            exit();
        }
        echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login | Digital Library</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
    body {
        font-family: 'Inter', sans-serif;
        background: #09090b;
        color: #fafafa;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        background-image: radial-gradient(ellipse at 60% 50%, rgba(239,68,68,0.08) 0%, transparent 70%);
    }
    .login-card {
        background: #18181b;
        border: 1px solid #27272a;
        border-radius: 16px;
        padding: 44px 40px;
        width: 380px;
        box-shadow: 0 25px 60px rgba(0,0,0,0.5);
    }
    .logo-row {
        display: flex; align-items: center; gap: 12px;
        margin-bottom: 32px;
    }
    .logo-box {
        width: 40px; height: 40px;
        background: #ef4444;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; font-weight: 800; color: #fff;
    }
    .logo-row span { font-size: 18px; font-weight: 700; }
    h1 { font-size: 22px; font-weight: 700; margin-bottom: 6px; }
    .subtitle { color: #71717a; font-size: 13.5px; margin-bottom: 28px; }
    label { display:block; font-size:12.5px; color:#a1a1aa; margin-bottom:5px; }
    input[type="text"], input[type="password"] {
        width: 100%;
        padding: 10px 14px;
        background: #09090b;
        border: 1px solid #27272a;
        color: #fafafa;
        border-radius: 8px;
        font-size: 14px;
        font-family: 'Inter', sans-serif;
        margin-bottom: 16px;
        transition: border 0.2s, box-shadow 0.2s;
    }
    input:focus {
        outline: none;
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239,68,68,0.1);
    }
    .btn-login {
        width: 100%;
        padding: 11px;
        background: #ef4444;
        border: none;
        color: white;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        font-family: 'Inter', sans-serif;
        margin-top: 6px;
        transition: background 0.2s, transform 0.15s;
    }
    .btn-login:hover { background: #dc2626; transform: translateY(-1px); }
    .btn-login:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
    .error-msg {
        color: #ef4444;
        font-size: 12.5px;
        display: none;
        margin-top: -10px;
        margin-bottom: 12px;
    }
    .error-msg.show { display: block; }
</style>
</head>
<body>

<div class="login-card">
    <div class="logo-row">
        <div class="logo-box">L</div>
        <span>LibraryAdmin</span>
    </div>
    <h1>Welcome back</h1>
    <p class="subtitle">Sign in to your admin panel</p>

    <form id="loginForm">
        <label>Username</label>
        <input type="text" id="username" placeholder="admin" autocomplete="username">
        <div id="usernameError" class="error-msg">Username is required.</div>

        <label>Password</label>
        <input type="password" id="password" placeholder="••••••••" autocomplete="current-password">
        <div id="passwordError" class="error-msg">Invalid username or password.</div>

        <button type="submit" id="loginBtn" class="btn-login">Sign In</button>
    </form>
</div>

<script>
const form     = document.getElementById('loginForm');
const username = document.getElementById('username');
const password = document.getElementById('password');
const userErr  = document.getElementById('usernameError');
const passErr  = document.getElementById('passwordError');
const loginBtn = document.getElementById('loginBtn');

form.addEventListener("submit", function(e) {
    e.preventDefault();
    userErr.classList.remove('show');
    passErr.classList.remove('show');

    let valid = true;

    if (!username.value.trim()) {
        userErr.textContent = "Username is required.";
        userErr.classList.add('show');
        valid = false;
    }
    if (!password.value) {
        passErr.textContent = "Password is required.";
        passErr.classList.add('show');
        valid = false;
    }

    if (valid) {
        loginBtn.textContent = "Signing in...";
        loginBtn.disabled = true;

        const formData = new FormData();
        formData.append('username', username.value.trim());
        formData.append('password', password.value);

        fetch('login.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = "Dashboard.php";
            } else {
                loginBtn.textContent = "Sign In";
                loginBtn.disabled = false;
                passErr.textContent = data.message || "Invalid username or password.";
                passErr.classList.add('show');
                password.value = "";
                password.focus();
            }
        })
        .catch(err => {
            loginBtn.textContent = "Sign In";
            loginBtn.disabled = false;
            passErr.textContent = "An error occurred. Please try again.";
            passErr.classList.add('show');
        });
    }
});
</script>

</body>
</html>