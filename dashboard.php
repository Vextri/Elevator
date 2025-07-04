<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit;
}

// Get login method for display
$login_method = $_SESSION['login_method'] ?? 'unknown';
$username = $_SESSION['username'] ?? 'Unknown User';
$user_id = $_SESSION['user_id'] ?? 'Unknown ID';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        body {
            font-family: sans-serif;
            background-color: #f4f4f4;
            padding: 40px;
        }
        .container {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
        }
        h1 {
            text-align: center;
        }
        .nav {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 30px;
        }
        .nav a {
            display: block;
            padding: 10px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
        }
        .nav a:hover {
            background: #0056b3;
        }
        .login-info {
            background: #e8f5e8;
            border: 1px solid #4caf50;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }
        .card-login {
            background: #e3f2fd;
            border-color: #2196f3;
        }
        .manual-login {
            background: #fff3e0;
            border-color: #ff9800;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    
    <!-- Login Method Information -->
    <div class="login-info <?php echo ($login_method === 'card_scan') ? 'card-login' : 'manual-login'; ?>">
        <p><strong>Login Method:</strong> 
            <?php if ($login_method === 'card_scan'): ?>
                🎓 Card Scan Login - Automatic Authentication
            <?php elseif ($login_method === 'manual_login'): ?>
                ⌨️ Manual Login - Username/Password
            <?php else: ?>
                🔍 <?php echo htmlspecialchars($login_method); ?>
            <?php endif; ?>
        </p>
        <?php if ($login_method === 'card_scan'): ?>
            <small style="color: #1976d2;">✅ Your student card was successfully recognized!</small>
        <?php endif; ?>
    </div>

    <div class="nav">
        <a href="user_requests.php">User approvals</a>
        <a href="index.php">Elevator control</a>
        <a href="jsdoom-dosbox/index.html" target="_blank">Play JS DOOM</a>
        <a href="logout.php">Logout</a>
        <a href="inside.php">Outside Elevator</a>
        <a href="outside.php">Inside Elevator</a>
    </div>
</div>
</body>
</html>
