<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
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
    </style>
</head>
<body>
<div class="container">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>

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
