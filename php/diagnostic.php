<?php
// diagnostic.php - Simple test to check if PHP is working
echo "<h1>🔍 PHP Diagnostic Test</h1>";
echo "<p><strong>Current Directory:</strong> " . __DIR__ . "</p>";
echo "<p><strong>Script Name:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";

echo "<h2>📂 File Check:</h2>";
$files_to_check = [
    'admin_lockout.php',
    'outside.php', 
    'index.php',
    'dashboard.php',
    'logout.php'
];

foreach ($files_to_check as $file) {
    $exists = file_exists($file);
    $readable = is_readable($file);
    echo "<p><strong>$file:</strong> " . 
         ($exists ? "✅ Exists" : "❌ Missing") . 
         ($readable ? " | ✅ Readable" : " | ❌ Not Readable") . "</p>";
}

echo "<h2>🔗 Test Links:</h2>";
echo '<p><a href="admin_lockout.php">🔐 Test Admin Lockout (Direct)</a></p>';
echo '<p><a href="outside.php">🚪 Test Outside Controls</a></p>';
echo '<p><a href="index.php">🏢 Test Main Elevator</a></p>';
echo '<p><a href="dashboard.php">📊 Test Dashboard</a></p>';

echo "<h2>📋 Session Info:</h2>";
session_start();
echo "<p><strong>Session Status:</strong> " . (session_status() === PHP_SESSION_ACTIVE ? "Active" : "Inactive") . "</p>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Logged In:</strong> " . (isset($_SESSION['user_id']) ? "Yes (User ID: " . $_SESSION['user_id'] . ")" : "No") . "</p>";
?>
