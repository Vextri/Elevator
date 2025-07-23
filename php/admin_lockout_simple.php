<?php
// admin_lockout_simple.php - Simplified version without auth checks
session_start();

echo "<h1>🔐 Admin Lockout - Simple Test</h1>";
echo "<p><strong>File:</strong> admin_lockout_simple.php</p>";
echo "<p><strong>Path:</strong> " . __FILE__ . "</p>";
echo "<p><strong>URL:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>HTTP Host:</strong> " . $_SERVER['HTTP_HOST'] . "</p>";

// Basic database connection test
echo "<h2>🗄️ Database Test:</h2>";
try {
    $mysqli = new mysqli("localhost", "root", "", "elevator_lockout_db");
    if ($mysqli->connect_error) {
        echo "<p>❌ Database connection failed: " . $mysqli->connect_error . "</p>";
    } else {
        echo "<p>✅ Database connected successfully</p>";
        
        // Test if table exists
        $result = $mysqli->query("SHOW TABLES LIKE 'elevator_lockout'");
        if ($result && $result->num_rows > 0) {
            echo "<p>✅ elevator_lockout table exists</p>";
            
            // Get lockout status
            $status = $mysqli->query("SELECT * FROM elevator_lockout WHERE elevator_id = 1 ORDER BY id DESC LIMIT 1");
            if ($status && $status->num_rows > 0) {
                $data = $status->fetch_assoc();
                echo "<p><strong>Current Status:</strong> " . ($data['is_locked_out'] ? "🔒 LOCKED" : "✅ UNLOCKED") . "</p>";
            } else {
                echo "<p>ℹ️ No lockout records</p>";
            }
        } else {
            echo "<p>❌ elevator_lockout table missing</p>";
        }
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "<p>❌ Exception: " . $e->getMessage() . "</p>";
}

echo "<h2>🔗 Navigation:</h2>";
echo '<p><a href="admin_lockout.php">🛡️ Full Admin Lockout Panel</a></p>';
echo '<p><a href="diagnostic.php">🔍 Diagnostic Page</a></p>';
echo '<p><a href="../html/test_lockout_integration.html">🧪 Back to Test Integration</a></p>';
?>
