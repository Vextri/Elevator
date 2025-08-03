<?php
// Start the session to access the flash message
session_start();

// Check if a flash message is set
if (isset($_SESSION['flash_message'])) {
    echo "<div class='alert alert-success'>" . $_SESSION['flash_message'] . "</div>";
    // Clear the flash message after displaying it
    unset($_SESSION['flash_message']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Requests - Elevator System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f4f4f4; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px #ccc; }
        .header { text-align: center; margin-bottom: 30px; }
        .back-link { display: inline-block; margin-bottom: 20px; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px; }
        .back-link:hover { background: #545b62; }
        .alert { padding: 15px; margin: 10px 0; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .request-item { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; background: #f8f9fa; }
        .approve-btn { background: #28a745; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 10px; }
        .approve-btn:hover { background: #218838; }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
        <div class="header">
            <h1>User Access Requests</h1>
            <p>Review and approve pending user access requests</p>
        </div>

<?php
// Connect to the database
$mysqli = new mysqli("localhost", "Blaise", "Gitdead32!32", "access_requests1");

// DEBUG: Show which database we're connected to
echo "<div style='background: #e3f2fd; padding: 10px; margin: 10px; border: 1px solid #2196f3;'>";
echo "<h3>DATABASE CONNECTION VALIDATION:</h3>";
$db_check = $mysqli->query("SELECT DATABASE() as current_db");
if ($db_check) {
    $db_name = $db_check->fetch_assoc();
    echo "Database Connected: " . $db_name['current_db'] . "<br>";
}
echo "</div>";

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Query to get all the pending requests
$query = "SELECT id, fullname, email, username, reason FROM requests WHERE approved = 0";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
    // Display each request
    while ($row = $result->fetch_assoc()) {
        echo "<div class='request-item'>";
        echo "<p><strong>Full Name:</strong> " . htmlspecialchars($row['fullname']) . "</p>";
        echo "<p><strong>Email:</strong> " . htmlspecialchars($row['email']) . "</p>";
        echo "<p><strong>Username:</strong> " . htmlspecialchars($row['username']) . "</p>";
        echo "<p><strong>Reason:</strong> " . htmlspecialchars($row['reason']) . "</p>";
        echo "<a href='approve_user.php?user_id=" . $row['id'] . "' class='approve-btn'>Approve User</a>";
        echo "</div>";
    }
} else {
    echo "<div class='request-item'><p>No pending requests.</p></div>";
}

$mysqli->close();
?>
    </div>
</body>
</html>
