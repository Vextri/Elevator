<?php
// Start the session to access the flash message
session_start();

// Check if a flash message is set
if (isset($_SESSION['flash_message'])) {
    echo "<div class='alert alert-success'>" . $_SESSION['flash_message'] . "</div>";
    // Clear the flash message after displaying it
    unset($_SESSION['flash_message']);
}

// Connect to the database
$mysqli = new mysqli("localhost", "Blaise", "Gitdead32!32", "access_requests1");

// DEBUG: Show which database we're connected to
echo "<div style='background: #e3f2fd; padding: 10px; margin: 10px; border: 1px solid #2196f3;'>";
echo "<h3>DATABASE CONNECTION DEBUG:</h3>";
echo "Connected to database: access_requests1<br>";
$db_check = $mysqli->query("SELECT DATABASE() as current_db");
if ($db_check) {
    $db_name = $db_check->fetch_assoc();
    echo "Actually connected to: " . $db_name['current_db'] . "<br>";
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
        echo "<div>";
        echo "<p><strong>Full Name:</strong> " . $row['fullname'] . "</p>";
        echo "<p><strong>Email:</strong> " . $row['email'] . "</p>";
        echo "<p><strong>Username:</strong> " . $row['username'] . "</p>";
        echo "<p><strong>Reason:</strong> " . $row['reason'] . "</p>";
        echo "<a href='approve_user.php?user_id=" . $row['id'] . "'>Approve</a>";  // Link to approve the request
        echo "</div><hr>";
    }
} else {
    echo "No pending requests.";
}

$mysqli->close();
?>
