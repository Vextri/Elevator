<?php
// diagnose_mysql.php - Diagnose and fix MySQL connection issues
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MySQL Connection Diagnostics</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            max-width: 1000px; 
            margin: 0 auto; 
            padding: 20px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        .status { 
            padding: 15px; 
            margin: 10px 0; 
            border-radius: 8px; 
            font-weight: bold;
        }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .fix-section { 
            border: 1px solid #ddd; 
            margin: 20px 0; 
            padding: 20px; 
            border-radius: 8px; 
            background: #f8f9fa;
        }
        button { 
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white; 
            padding: 15px 30px; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            font-size: 16px;
            margin: 5px;
        }
        button:hover { background: linear-gradient(45deg, #0056b3, #004085); }
        .code-block {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 10px;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
            white-space: pre-wrap;
        }
        .step {
            background: #e9ecef;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1> MySQL Connection Diagnostics</h1>
        <p>This tool will help diagnose and fix MySQL connection issues on your XAMPP installation.</p>
        
        <?php if (!isset($_POST['diagnose']) && !isset($_POST['test_connection'])): ?>
        
        <div class="fix-section">
            <h2>📋 Common Issues & Solutions</h2>
            <div class="step">
                <h3>1. MySQL Service Not Running</h3>
                <p><strong>Solution:</strong> Start XAMPP Control Panel and click "Start" next to MySQL</p>
            </div>
            
            <div class="step">
                <h3>2. Root User Has Password</h3>
                <p><strong>Solution:</strong> Either provide the password or reset it</p>
            </div>
            
            <div class="step">
                <h3>3. MySQL Configuration Issues</h3>
                <p><strong>Solution:</strong> Reset MySQL configuration or recreate root user</p>
            </div>
        </div>
        
        <form method="POST">
            <button type="submit" name="diagnose">Run Full Diagnostics</button>
        </form>
        
        <div class="fix-section">
            <h2>🧪 Test MySQL Connection</h2>
            <p>Test different connection methods to find what works:</p>
            <form method="POST">
                <input type="password" name="test_password" placeholder="MySQL root password (leave empty to test no password)">
                <button type="submit" name="test_connection">🔌 Test Connection</button>
            </form>
        </div>
        
        <?php elseif (isset($_POST['diagnose'])): ?>
        
        <div class="container">
            <h2> Running MySQL Diagnostics...</h2>
            
            <?php
            echo "<h3>📊 System Information</h3>";
            echo "<div class='info'>PHP Version: " . phpversion() . "</div>";
            echo "<div class='info'>Operating System: " . php_uname() . "</div>";
            
            // Check if mysqli extension is loaded
            if (extension_loaded('mysqli')) {
                echo "<div class='status success'>✅ MySQLi extension is loaded</div>";
            } else {
                echo "<div class='status error'>❌ MySQLi extension is not loaded</div>";
            }
            
            // Test different connection methods
            echo "<h3>🔌 Testing Connection Methods</h3>";
            
            $connection_tests = [
                ['host' => 'localhost', 'user' => 'root', 'password' => '', 'description' => 'Default XAMPP (no password)'],
                ['host' => '127.0.0.1', 'user' => 'root', 'password' => '', 'description' => 'IP address (no password)'],
                ['host' => 'localhost', 'user' => 'root', 'password' => 'root', 'description' => 'Common password: root'],
                ['host' => 'localhost', 'user' => 'root', 'password' => 'admin', 'description' => 'Common password: admin'],
                ['host' => 'localhost', 'user' => 'root', 'password' => '', 'description' => 'Socket connection', 'socket' => true]
            ];
            
            $working_connection = null;
            
            foreach ($connection_tests as $test) {
                echo "<div class='step'>";
                echo "<strong>Testing:</strong> " . htmlspecialchars($test['description']) . "<br>";
                
                try {
                    if (isset($test['socket'])) {
                        $mysqli = new mysqli($test['host'], $test['user'], $test['password'], '', 3306, '/tmp/mysql.sock');
                    } else {
                        $mysqli = new mysqli($test['host'], $test['user'], $test['password']);
                    }
                    
                    if ($mysqli->connect_error) {
                        echo "<div class='status error'>❌ Failed: " . htmlspecialchars($mysqli->connect_error) . "</div>";
                    } else {
                        echo "<div class='status success'>✅ Success! Connection works</div>";
                        
                        // Get MySQL version
                        $version = $mysqli->server_info;
                        echo "<div class='info'>MySQL Version: $version</div>";
                        
                        // Check privileges
                        $result = $mysqli->query("SHOW GRANTS FOR CURRENT_USER()");
                        if ($result) {
                            echo "<div class='info'>User Privileges:</div>";
                            echo "<div class='code-block'>";
                            while ($row = $result->fetch_row()) {
                                echo htmlspecialchars($row[0]) . "\n";
                            }
                            echo "</div>";
                        }
                        
                        $working_connection = $test;
                        $mysqli->close();
                        break;
                    }
                } catch (Exception $e) {
                    echo "<div class='status error'>❌ Exception: " . htmlspecialchars($e->getMessage()) . "</div>";
                }
                echo "</div>";
            }
            
            if ($working_connection) {
                echo "<div class='status success'>🎉 Found working connection: " . htmlspecialchars($working_connection['description']) . "</div>";
                echo "<form method='POST' action='setup_all_databases.php'>";
                echo "<input type='hidden' name='root_password' value='" . htmlspecialchars($working_connection['password']) . "'>";
                echo "<button type='submit'>🚀 Use This Connection for Setup</button>";
                echo "</form>";
            } else {
                echo "<div class='status error'>❌ No working connection found. Please see manual fixes below.</div>";
            }
            ?>
            
            <div class="fix-section">
                <h2>🔧 Manual Fixes</h2>
                
                <div class="step">
                    <h3>Option 1: Reset MySQL Root Password</h3>
                    <p>1. Stop MySQL in XAMPP Control Panel</p>
                    <p>2. Open Command Prompt as Administrator</p>
                    <p>3. Navigate to XAMPP MySQL bin directory:</p>
                    <div class="code-block">cd C:\xampp\mysql\bin</div>
                    <p>4. Start MySQL in safe mode:</p>
                    <div class="code-block">mysqld --skip-grant-tables --skip-networking</div>
                    <p>5. Open another Command Prompt and run:</p>
                    <div class="code-block">mysql -u root</div>
                    <p>6. Reset the password:</p>
                    <div class="code-block">UPDATE mysql.user SET Password=PASSWORD('') WHERE User='root';
FLUSH PRIVILEGES;
EXIT;</div>
                    <p>7. Restart MySQL service in XAMPP</p>
                </div>
                
                <div class="step">
                    <h3>Option 2: Recreate MySQL Installation</h3>
                    <p>1. Backup any important databases first</p>
                    <p>2. Stop all XAMPP services</p>
                    <p>3. Delete the mysql/data folder (C:\xampp\mysql\data)</p>
                    <p>4. Copy mysql/backup folder to mysql/data</p>
                    <p>5. Start MySQL service</p>
                </div>
                
                <div class="step">
                    <h3>Option 3: Use phpMyAdmin</h3>
                    <p>1. If phpMyAdmin works, you can create users there</p>
                    <p>2. Go to http://localhost/phpmyadmin</p>
                    <p>3. Create the required databases manually</p>
                </div>
            </div>
        </div>
        
        <?php elseif (isset($_POST['test_connection'])): ?>
        
        <div class="container">
            <h2>🧪 Testing Specific Connection...</h2>
            
            <?php
            $test_password = $_POST['test_password'] ?? '';
            
            echo "<h3>Testing with password: " . (empty($test_password) ? "(no password)" : "***") . "</h3>";
            
            try {
                $mysqli = new mysqli("localhost", "root", $test_password);
                if ($mysqli->connect_error) {
                    echo "<div class='status error'>❌ Connection failed: " . htmlspecialchars($mysqli->connect_error) . "</div>";
                    
                    // Provide specific error solutions
                    if (strpos($mysqli->connect_error, 'Access denied') !== false) {
                        echo "<div class='warning'>💡 This is a password issue. Try:</div>";
                        echo "<ul>";
                        echo "<li>Leave password empty for default XAMPP</li>";
                        echo "<li>Try common passwords: root, admin, password</li>";
                        echo "<li>Check if you set a custom password</li>";
                        echo "</ul>";
                    } elseif (strpos($mysqli->connect_error, 'Connection refused') !== false) {
                        echo "<div class='warning'>💡 MySQL service is not running. Start it in XAMPP Control Panel.</div>";
                    }
                } else {
                    echo "<div class='status success'>✅ Connection successful!</div>";
                    echo "<div class='info'>MySQL Version: " . $mysqli->server_info . "</div>";
                    
                    // Test database operations
                    echo "<h4>Testing Database Operations:</h4>";
                    
                    $test_db = "test_connection_" . time();
                    if ($mysqli->query("CREATE DATABASE $test_db")) {
                        echo "<div class='status success'>✅ Can create databases</div>";
                        $mysqli->query("DROP DATABASE $test_db");
                        echo "<div class='status success'>✅ Can drop databases</div>";
                    } else {
                        echo "<div class='status error'>❌ Cannot create databases: " . $mysqli->error . "</div>";
                    }
                    
                    echo "<form method='POST' action='setup_all_databases.php'>";
                    echo "<input type='hidden' name='root_password' value='" . htmlspecialchars($test_password) . "'>";
                    echo "<button type='submit'>🚀 Use This Password for Setup</button>";
                    echo "</form>";
                    
                    $mysqli->close();
                }
            } catch (Exception $e) {
                echo "<div class='status error'>❌ Exception: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
            ?>
        </div>
        
        <?php endif; ?>
        
        <div class="container">
            <h2>🔗 Quick Links</h2>
            <a href="setup_all_databases.php"><button>🚀 Go to Main Setup</button></a>
            <a href="http://localhost/phpmyadmin" target="_blank"><button>🗃️ Open phpMyAdmin</button></a>
            <a href="check_database_structure.php"><button>📊 Check Database Status</button></a>
        </div>
    </div>
</body>
</html>
