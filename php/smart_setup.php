<?php
// smart_setup.php - Intelligent setup that handles various MySQL configurations
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Elevator System Setup</title>
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
        .setup-section { 
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
        .progress-bar {
            width: 100%;
            height: 20px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }
        .progress {
            height: 100%;
            background: linear-gradient(45deg, #28a745, #20c997);
            width: 0%;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Smart Elevator System Setup</h1>
        <p>This intelligent setup will automatically detect your MySQL configuration and set up all required databases.</p>
        
        <?php if (!isset($_POST['start_setup'])): ?>
        
        <div class="setup-section">
            <h2>What Will Be Set Up</h2>
            <ul>
                <li><strong>User Management System</strong> - Authentication and access control</li>
                <li><strong>Safety Lockout System</strong> - LOTO (Lockout/Tagout) controls</li>
                <li><strong>Elevator Control System</strong> - Movement tracking and API</li>
                <li><strong>Default Admin Account</strong> - Username: Admin123, Password: Admin123!</li>
            </ul>
        </div>
        
        <div class="setup-section">
            <h2>Pre-Setup Check</h2>
            <p>Click below to automatically detect your MySQL configuration and run setup:</p>
            <form method="POST">
                <button type="submit" name="start_setup">Auto-Detect & Setup</button>
            </form>
        </div>
        
        <div class="setup-section">
            <h2>Manual Options</h2>
            <a href="diagnose_mysql.php"><button type="button">Diagnose MySQL Issues</button></a>
            <a href="setup_all_databases.php"><button type="button">Manual Setup</button></a>
            <a href="documentation.php"><button type="button">Complete Documentation</button></a>
        </div>
        
        <?php else: ?>
        
        <div class="container">
            <h2>Running Smart Setup...</h2>
            <div class="progress-bar">
                <div class="progress" id="progress"></div>
            </div>
            
            <?php
            function findWorkingConnection() {
                $connection_tests = [
                    ['host' => 'localhost', 'user' => 'root', 'password' => '', 'description' => 'Default XAMPP'],
                    ['host' => '127.0.0.1', 'user' => 'root', 'password' => '', 'description' => 'IP localhost'],
                    ['host' => 'localhost', 'user' => 'root', 'password' => 'root', 'description' => 'Password: root'],
                    ['host' => 'localhost', 'user' => 'root', 'password' => 'admin', 'description' => 'Password: admin'],
                    ['host' => 'localhost', 'user' => 'root', 'password' => 'password', 'description' => 'Password: password'],
                    ['host' => 'localhost', 'user' => 'root', 'password' => 'mysql', 'description' => 'Password: mysql']
                ];
                
                foreach ($connection_tests as $test) {
                    try {
                        $mysqli = new mysqli($test['host'], $test['user'], $test['password']);
                        if (!$mysqli->connect_error) {
                            $test['connection'] = $mysqli;
                            return $test;
                        }
                    } catch (Exception $e) {
                        continue;
                    }
                }
                return null;
            }
            
            echo "<script>document.getElementById('progress').style.width = '10%';</script>";
            echo "<div class='info'>Step 1: Detecting MySQL configuration...</div>";
            flush();
            
            $working_connection = findWorkingConnection();
            
            if (!$working_connection) {
                echo "<div class='status error'>Could not connect to MySQL with any common configuration.</div>";
                echo "<div class='warning'>Please check:</div>";
                echo "<ul>";
                echo "<li>XAMPP MySQL service is running</li>";
                echo "<li>No custom MySQL password was set</li>";
                echo "<li>MySQL port 3306 is not blocked</li>";
                echo "</ul>";
                echo "<a href='diagnose_mysql.php'><button> Run Detailed Diagnostics</button></a>";
                exit;
            }
            
            echo "<div class='status success'>Connected using: " . htmlspecialchars($working_connection['description']) . "</div>";
            echo "<script>document.getElementById('progress').style.width = '25%';</script>";
            flush();
            
            $mysqli = $working_connection['connection'];
            $root_password = $working_connection['password'];
            
            // Hash password for admin accounts
            $hashed_password = password_hash('Admin123!', PASSWORD_DEFAULT);
            
            try {
                // Setup 1: User Management Database
                echo "<div class='info'>Step 2: Setting up User Management Database...</div>";
                echo "<script>document.getElementById('progress').style.width = '40%';</script>";
                flush();
                
                $access_sql = "
                    CREATE DATABASE IF NOT EXISTS access_requests1;
                    CREATE USER IF NOT EXISTS 'Blaise'@'localhost' IDENTIFIED BY 'Gitdead32!32';
                    GRANT ALL PRIVILEGES ON access_requests1.* TO 'Blaise'@'localhost';
                    FLUSH PRIVILEGES;
                ";
                
                if ($mysqli->multi_query($access_sql)) {
                    do {
                        if ($result = $mysqli->store_result()) {
                            $result->free();
                        }
                    } while ($mysqli->next_result());
                    echo "<div class='status success'>User management database created</div>";
                } else {
                    throw new Exception("Failed to create access_requests1: " . $mysqli->error);
                }
                
                // Connect as Blaise to create tables
                $user_mysqli = new mysqli("localhost", "Blaise", "Gitdead32!32", "access_requests1");
                if ($user_mysqli->connect_error) {
                    throw new Exception("Could not connect as Blaise user: " . $user_mysqli->connect_error);
                }
                
                $tables_sql = "
                    CREATE TABLE IF NOT EXISTS requests (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        fullname VARCHAR(255) NOT NULL,
                        username VARCHAR(255) UNIQUE NOT NULL,
                        email VARCHAR(255) UNIQUE NOT NULL,
                        password VARCHAR(255) NOT NULL,
                        student_card VARCHAR(255) UNIQUE,
                        reason TEXT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        approved BOOLEAN DEFAULT FALSE,
                        INDEX idx_username (username),
                        INDEX idx_email (email),
                        INDEX idx_student_card (student_card),
                        INDEX idx_fullname (fullname)
                    );
                    
                    CREATE TABLE IF NOT EXISTS access_logs (
                        log_id INT AUTO_INCREMENT PRIMARY KEY,
                        student_card VARCHAR(255),
                        access_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        success BOOLEAN DEFAULT TRUE,
                        reason TEXT,
                        ip_address VARCHAR(45),
                        user_agent TEXT
                    );
                ";
                
                if ($user_mysqli->multi_query($tables_sql)) {
                    do {
                        if ($result = $user_mysqli->store_result()) {
                            $result->free();
                        }
                    } while ($user_mysqli->next_result());
                    echo "<div class='status success'>User management tables created</div>";
                } else {
                    throw new Exception("Failed to create user tables: " . $user_mysqli->error);
                }
                
                // Insert default users
                $admin_stmt = $user_mysqli->prepare("INSERT IGNORE INTO requests (username, email, password, student_card, reason, approved) VALUES (?, ?, ?, ?, ?, ?)");
                $admin_stmt->bind_param("sssssi", 
                    $admin_user = 'Admin123',
                    $admin_email = 'admin@elevator.local',
                    $hashed_password,
                    $admin_card = 'ADMIN001',
                    $admin_reason = 'System administrator account',
                    $admin_approved = 1
                );
                $admin_stmt->execute();
                
                $dev_stmt = $user_mysqli->prepare("INSERT IGNORE INTO requests (username, email, password, student_card, reason, approved) VALUES (?, ?, ?, ?, ?, ?)");
                $dev_stmt->bind_param("sssssi",
                    $dev_user = 'bswan',
                    $dev_email = 'bswan8085@conestogac.on.ca',
                    $hashed_password,
                    $dev_card = 'DEV001',
                    $dev_reason = 'Developer account',
                    $dev_approved = 1
                );
                $dev_stmt->execute();
                
                echo "<div class='status success'>Default admin accounts created</div>";
                $user_mysqli->close();
                
                echo "<script>document.getElementById('progress').style.width = '60%';</script>";
                flush();
                
                // Setup 2: Lockout Database
                echo "<div class='info'>Step 3: Setting up Safety Lockout System...</div>";
                
                $lockout_sql = "
                    CREATE DATABASE IF NOT EXISTS elevator_lockout_db;
                    USE elevator_lockout_db;
                    CREATE TABLE IF NOT EXISTS elevator_lockout (
                        id INT PRIMARY KEY DEFAULT 1,
                        is_locked BOOLEAN DEFAULT FALSE,
                        locked_by VARCHAR(255) DEFAULT NULL,
                        locked_at TIMESTAMP NULL DEFAULT NULL,
                        reason TEXT DEFAULT NULL,
                        CHECK (id = 1)
                    );
                    INSERT IGNORE INTO elevator_lockout (id, is_locked) VALUES (1, FALSE);
                ";
                
                if ($mysqli->multi_query($lockout_sql)) {
                    do {
                        if ($result = $mysqli->store_result()) {
                            $result->free();
                        }
                    } while ($mysqli->next_result());
                    echo "<div class='status success'>Safety lockout system created</div>";
                } else {
                    throw new Exception("Failed to create lockout database: " . $mysqli->error);
                }
                
                echo "<script>document.getElementById('progress').style.width = '80%';</script>";
                flush();
                
                // Setup 3: Elevator Control Database
                echo "<div class='info'>Step 4: Setting up Elevator Control System...</div>";
                
                $elevator_sql = "
                    CREATE DATABASE IF NOT EXISTS elevator;
                    CREATE USER IF NOT EXISTS 'ese'@'localhost' IDENTIFIED BY 'ese';
                    GRANT ALL PRIVILEGES ON elevator.* TO 'ese'@'localhost';
                    FLUSH PRIVILEGES;
                ";
                
                if ($mysqli->multi_query($elevator_sql)) {
                    do {
                        if ($result = $mysqli->store_result()) {
                            $result->free();
                        }
                    } while ($mysqli->next_result());
                    echo "<div class='status success'>Elevator control database created</div>";
                } else {
                    throw new Exception("Failed to create elevator database: " . $mysqli->error);
                }
                
                // Connect as ese user to create elevator table
                $elevator_mysqli = new mysqli("localhost", "ese", "ese", "elevator");
                if ($elevator_mysqli->connect_error) {
                    throw new Exception("Could not connect as ese user: " . $elevator_mysqli->connect_error);
                }
                
                $elevator_table_sql = "
                    CREATE TABLE IF NOT EXISTS elevatorNetwork (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        current_floor INT NOT NULL DEFAULT 1,
                        target_floor INT DEFAULT NULL,
                        status VARCHAR(50) DEFAULT 'idle',
                        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    );
                    INSERT IGNORE INTO elevatorNetwork (id, current_floor, status) VALUES (1, 1, 'idle');
                ";
                
                if ($elevator_mysqli->multi_query($elevator_table_sql)) {
                    do {
                        if ($result = $elevator_mysqli->store_result()) {
                            $result->free();
                        }
                    } while ($elevator_mysqli->next_result());
                    echo "<div class='status success'>Elevator control table created</div>";
                } else {
                    throw new Exception("Failed to create elevator table: " . $elevator_mysqli->error);
                }
                
                $elevator_mysqli->close();
                
                echo "<script>document.getElementById('progress').style.width = '100%';</script>";
                flush();
                
                echo "<div class='status success'>Setup completed successfully!</div>";
                
            } catch (Exception $e) {
                echo "<div class='status error'>Setup failed: " . htmlspecialchars($e->getMessage()) . "</div>";
                echo "<div class='warning'>You can try the manual setup or diagnose the issue.</div>";
            }
            
            $mysqli->close();
            ?>
            
            <div class="setup-section">
                <h2>Login Information</h2>
                <div class="status info">
                    <strong>Default Admin Account:</strong><br>
                    Username: <strong>Admin123</strong><br>
                    Password: <strong>Admin123!</strong>
                </div>
                <div class="status info">
                    <strong>Developer Account:</strong><br>
                    Username: <strong>bswan</strong><br>
                    Password: <strong>Admin123!</strong>
                </div>
            </div>
            
            <div class="setup-section">
                <h2>Next Steps</h2>
                <a href="login1.php"><button>Test Login</button></a>
                <a href="index.php"><button>Open Elevator Interface</button></a>
                <a href="admin_lockout.php"><button>Test Lockout System</button></a>
                <a href="check_database_structure.php"><button>Verify Database Structure</button></a>
            </div>
        </div>
        
        <?php endif; ?>
    </div>
    
    <script>
        // Auto-refresh progress bar
        if (document.getElementById('progress')) {
            setTimeout(function() {
                document.getElementById('progress').style.width = '100%';
            }, 5000);
        }
    </script>
</body>
</html>
