<?php
// admin_lockout_debug.php - Debug version of admin lockout panel
session_start();

echo "<h1>🔐 Admin Lockout Panel - Debug Mode</h1>";

// Check session
echo "<h2>📋 Session Check:</h2>";
echo "<p><strong>Session Active:</strong> " . (session_status() === PHP_SESSION_ACTIVE ? "✅ Yes" : "❌ No") . "</p>";
echo "<p><strong>User ID Set:</strong> " . (isset($_SESSION['user_id']) ? "✅ Yes (" . $_SESSION['user_id'] . ")" : "❌ No") . "</p>";
echo "<p><strong>Username Set:</strong> " . (isset($_SESSION['username']) ? "✅ Yes (" . $_SESSION['username'] . ")" : "❌ No") . "</p>";

// Test database connections
echo "<h2>🗄️ Database Connection Tests:</h2>";

// Test lockout database
echo "<h3>Lockout Database (elevator_lockout_db):</h3>";
try {
    $mysqli = new mysqli("localhost", "root", "", "elevator_lockout_db");
    if ($mysqli->connect_error) {
        echo "<p>❌ Connection failed: " . $mysqli->connect_error . "</p>";
    } else {
        echo "<p>✅ Connected successfully</p>";
        
        // Test table exists
        $result = $mysqli->query("SHOW TABLES LIKE 'elevator_lockout'");
        if ($result && $result->num_rows > 0) {
            echo "<p>✅ elevator_lockout table exists</p>";
            
            // Get current lockout status
            $status = $mysqli->query("SELECT * FROM elevator_lockout WHERE elevator_id = 1 ORDER BY lockout_timestamp DESC LIMIT 1");
            if ($status && $status->num_rows > 0) {
                $lockout_data = $status->fetch_assoc();
                echo "<p><strong>Current Status:</strong> " . 
                     ($lockout_data['is_locked_out'] ? "🔒 LOCKED OUT" : "✅ OPERATIONAL") . "</p>";
                if ($lockout_data['is_locked_out']) {
                    echo "<p><strong>Reason:</strong> " . htmlspecialchars($lockout_data['lockout_reason']) . "</p>";
                    echo "<p><strong>Locked by:</strong> " . htmlspecialchars($lockout_data['locked_by_username']) . "</p>";
                }
            } else {
                echo "<p>ℹ️ No lockout records found</p>";
            }
        } else {
            echo "<p>❌ elevator_lockout table does not exist</p>";
        }
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "<p>❌ Exception: " . $e->getMessage() . "</p>";
}

// Test user database
echo "<h3>User Database (access_requests1):</h3>";
try {
    $user_mysqli = new mysqli("localhost", "Blaise", "Gitdead32!32", "access_requests1");
    if ($user_mysqli->connect_error) {
        echo "<p>❌ Connection failed: " . $user_mysqli->connect_error . "</p>";
    } else {
        echo "<p>✅ Connected successfully</p>";
        
        // Test if user exists (if logged in)
        if (isset($_SESSION['user_id'])) {
            $user_check = $user_mysqli->prepare("SELECT username, email FROM requests WHERE id = ?");
            $user_check->bind_param("i", $_SESSION['user_id']);
            $user_check->execute();
            $result = $user_check->get_result();
            $user_data = $result->fetch_assoc();
            
            if ($user_data) {
                echo "<p>✅ User found: " . htmlspecialchars($user_data['username']) . "</p>";
            } else {
                echo "<p>❌ User ID " . $_SESSION['user_id'] . " not found in database</p>";
            }
        } else {
            echo "<p>ℹ️ No user session to check</p>";
        }
        $user_mysqli->close();
    }
} catch (Exception $e) {
    echo "<p>❌ Exception: " . $e->getMessage() . "</p>";
}

echo "<h2>🔗 Navigation:</h2>";
echo '<p><a href="dashboard.php">📊 Back to Dashboard</a></p>';
echo '<p><a href="../html/login.html">🔐 Login Page</a></p>';
echo '<p><a href="admin_lockout.php">🛡️ Try Original Admin Lockout</a></p>';

// If user is logged in, show simplified lockout controls
if (isset($_SESSION['user_id'])) {
    echo "<h2>🛡️ Quick Lockout Controls:</h2>";
    echo '<form method="post" action="admin_lockout.php">';
    echo '<label>Reason: <input type="text" name="reason" value="Debug test lockout" required></label><br><br>';
    echo '<button type="submit" name="action" value="lockout">🔒 Lock Elevator</button> ';
    echo '<button type="submit" name="action" value="unlock">✅ Unlock Elevator</button>';
    echo '</form>';
}
?>
