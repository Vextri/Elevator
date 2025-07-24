<?php
// test_lockout_database.php - Test the new lockout database connection
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Lockout Database Connection</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        .success { color: #155724; background: #d4edda; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .error { color: #721c24; background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 4px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Lockout Database Connection Test</h1>
        
        <?php
        echo "<div class='info'><strong>Testing connection to:</strong><br>";
        echo "Database: elevator_lockout_db<br>";
        echo "User: Blaise<br>";
        echo "Host: localhost</div>";
        
        try {
            // Test database connection
            $pdo = new PDO('mysql:host=localhost;dbname=elevator_lockout_db', 'Blaise', 'Gitdead32!32');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            echo "<div class='success'>✅ Database connection successful!</div>";
            
            // Test lockout table
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo "<div class='info'><strong>Tables found:</strong><br>";
            foreach ($tables as $table) {
                echo "• $table<br>";
            }
            echo "</div>";
            
            // Test lockout data
            if (in_array('elevator_lockout', $tables)) {
                echo "<h3>Lockout Table Data:</h3>";
                $stmt = $pdo->query("SELECT * FROM elevator_lockout ORDER BY id DESC LIMIT 5");
                $lockouts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if ($lockouts) {
                    echo "<table>";
                    echo "<tr><th>ID</th><th>Elevator ID</th><th>Locked Out</th><th>Reason</th><th>Locked By</th><th>Timestamp</th></tr>";
                    foreach ($lockouts as $lockout) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($lockout['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($lockout['elevator_id']) . "</td>";
                        echo "<td>" . ($lockout['is_locked_out'] ? '🔒 YES' : '✅ NO') . "</td>";
                        echo "<td>" . htmlspecialchars($lockout['lockout_reason'] ?? 'N/A') . "</td>";
                        echo "<td>" . htmlspecialchars($lockout['locked_by_username'] ?? 'N/A') . "</td>";
                        echo "<td>" . htmlspecialchars($lockout['lockout_timestamp'] ?? 'N/A') . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<div class='info'>No lockout records found yet.</div>";
                }
            }
            
            // Test users table
            if (in_array('users', $tables)) {
                echo "<h3>Users Table Data:</h3>";
                $stmt = $pdo->query("SELECT id, username, email, role FROM users LIMIT 5");
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if ($users) {
                    echo "<table>";
                    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th></tr>";
                    foreach ($users as $user) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($user['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($user['role']) . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<div class='info'>No users found yet.</div>";
                }
            }
            
            echo "<div class='success'><strong>✅ Database test completed successfully!</strong><br>";
            echo "The lockout system database is ready to use.</div>";
            
        } catch (PDOException $e) {
            echo "<div class='error'>❌ Database connection failed!<br>";
            echo "<strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "<br><br>";
            echo "<strong>Possible solutions:</strong><br>";
            echo "• Make sure MySQL is running<br>";
            echo "• Run the setup_lockout_database.sql script first<br>";
            echo "• Check that user 'Blaise' has the correct password<br>";
            echo "• Verify the database 'elevator_lockout_db' exists</div>";
        }
        ?>
        
        <div class='info'>
            <strong>Next Steps:</strong><br>
            1. If this test passes, your lockout database is ready<br>
            2. Visit <a href="admin_lockout.php">admin_lockout.php</a> to manage lockouts<br>
            3. Test the elevator interfaces to see lockout status<br>
            4. Visit <a href="../html/test_lockout_integration.html">test_lockout_integration.html</a> for full testing
        </div>
    </div>
</body>
</html>
