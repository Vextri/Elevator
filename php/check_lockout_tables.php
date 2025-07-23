<?php
// Quick check of lockout database tables
try {
    $mysqli = new mysqli("localhost", "root", "", "elevator_lockout_db");
    if ($mysqli->connect_error) {
        echo "Connection failed: " . $mysqli->connect_error;
        exit;
    }
    
    echo "<h2>Tables in elevator_lockout_db:</h2>";
    $result = $mysqli->query("SHOW TABLES");
    if ($result->num_rows > 0) {
        echo "<ul>";
        while ($row = $result->fetch_array()) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No tables found!</p>";
    }
    
    // Check if access_logs table exists specifically
    $check = $mysqli->query("SHOW TABLES LIKE 'access_logs'");
    if ($check->num_rows == 0) {
        echo "<p style='color: red;'><strong>❌ access_logs table is missing!</strong></p>";
        echo "<p>You need to run the database setup to create this table.</p>";
    } else {
        echo "<p style='color: green;'><strong>✅ access_logs table exists!</strong></p>";
    }
    
    $mysqli->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
