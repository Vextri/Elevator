<?php
// quick_mysql_test.php - Simple test to determine root password
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quick MySQL Root Password Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        .test-result { padding: 10px; margin: 10px 0; border-radius: 5px; font-weight: bold; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .info { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Quick MySQL Root Password Test</h1>
        <p>This will test common root passwords to determine what your MySQL is using.</p>
        
        <?php
        $test_passwords = [
            '' => 'No password (default XAMPP)',
            'root' => 'Password: root',
            'admin' => 'Password: admin', 
            'password' => 'Password: password',
            'mysql' => 'Password: mysql',
            'xampp' => 'Password: xampp'
        ];
        
        $working_password = null;
        $working_description = null;
        
        foreach ($test_passwords as $password => $description) {
            echo "<div class='test-result info'>Testing: $description</div>";
            
            try {
                $mysqli = new mysqli("localhost", "root", $password);
                if (!$mysqli->connect_error) {
                    echo "<div class='test-result success'>✅ SUCCESS! Root password is: " . ($password === '' ? '(empty/no password)' : $password) . "</div>";
                    $working_password = $password;
                    $working_description = $description;
                    
                    // Test if we can create databases
                    if ($mysqli->query("SHOW DATABASES")) {
                        echo "<div class='test-result success'>✅ Can access databases</div>";
                    }
                    
                    $mysqli->close();
                    break;
                } else {
                    echo "<div class='test-result error'>❌ Failed: " . htmlspecialchars($mysqli->connect_error) . "</div>";
                }
            } catch (Exception $e) {
                echo "<div class='test-result error'>❌ Exception: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
        
        if ($working_password !== null) {
            echo "<hr>";
            echo "<h2>🎯 Solution Found!</h2>";
            echo "<div class='test-result success'>";
            echo "<strong>Your MySQL root password is:</strong> " . ($working_password === '' ? '(empty)' : $working_password) . "<br>";
            echo "<strong>Now you can:</strong><br>";
            echo "1. Use this password in the setup scripts<br>";
            echo "2. Or reset it to empty if you prefer<br>";
            echo "</div>";
            
            echo "<h3>🚀 Quick Setup Options:</h3>";
            echo "<form method='POST' action='setup_all_databases.php' style='margin: 10px 0;'>";
            echo "<input type='hidden' name='root_password' value='" . htmlspecialchars($working_password) . "'>";
            echo "<button type='submit' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>🚀 Run Setup with This Password</button>";
            echo "</form>";
            
            if ($working_password !== '') {
                echo "<h3>🔧 Or Reset Password to Empty:</h3>";
                echo "<p>If you want to use the default XAMPP setup (no password), you can reset it:</p>";
                echo "<a href='reset_mysql_password.bat' download style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>💾 Download Password Reset Tool</a>";
            }
            
        } else {
            echo "<hr>";
            echo "<div class='test-result error'>";
            echo "<strong>❌ Could not connect with any common password</strong><br>";
            echo "This might indicate:<br>";
            echo "• MySQL service is not running<br>";
            echo "• Custom password was set<br>";
            echo "• MySQL configuration issue<br>";
            echo "</div>";
            
            echo "<h3>🔧 Next Steps:</h3>";
            echo "<p>1. Make sure XAMPP MySQL service is running</p>";
            echo "<p>2. Try the password reset tool:</p>";
            echo "<a href='reset_mysql_password.bat' download style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>💾 Download Password Reset Tool</a>";
            echo "<p>3. Or use the comprehensive diagnostic tool:</p>";
            echo "<a href='diagnose_mysql.php' style='background: #ffc107; color: black; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Full Diagnostics</a>";
        }
        ?>
        
        <hr>
        <h3>📋 Current System Status:</h3>
        <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
        <p><strong>MySQLi Extension:</strong> <?php echo extension_loaded('mysqli') ? '✅ Loaded' : '❌ Not loaded'; ?></p>
        <p><strong>Server:</strong> <?php echo $_SERVER['HTTP_HOST']; ?></p>
        
        <hr>
        <p><a href="setup_center.php">← Back to Setup Center</a></p>
    </div>
</body>
</html>
