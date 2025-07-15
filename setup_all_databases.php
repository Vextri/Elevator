<?php
// setup_all_databases.php - Complete setup for all elevator system databases
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Elevator System Setup</title>
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
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
        }
        button:hover { 
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 123, 255, 0.4);
        }
        .disabled { background: #6c757d; cursor: not-allowed; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 8px; overflow-x: auto; border: 1px solid #e9ecef; }
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            margin: 10px 0;
        }
        input[type="password"]:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }
        .credentials {
            background: #e7f3ff;
            border: 2px solid #007bff;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        h1 { color: #2c3e50; text-align: center; margin-bottom: 30px; }
        h2 { color: #495057; }
        ul li { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚁 Complete Elevator System Setup</h1>
        <p style="text-align: center; font-size: 18px; color: #6c757d;">
            One-click setup for all elevator system databases
        </p>
    </div>
    
    <?php if ($_SERVER['REQUEST_METHOD'] !== 'POST'): ?>
    
    <div class="container">
        <div class="setup-section">
            <h2>📋 Setup Overview</h2>
            <p>This setup will create and configure all three databases needed for the elevator system:</p>
            <ul>
                <li><strong>🔐 access_requests1</strong> - User management and authentication system</li>
                <li><strong>🔒 elevator_lockout_db</strong> - Lockout/tagout safety system</li>
                <li><strong>🚁 elevator</strong> - Elevator movement and status tracking</li>
            </ul>
        </div>

        <div class="credentials">
            <h3>🎯 Default Login Credentials</h3>
            <p><strong>Username:</strong> Admin123</p>
            <p><strong>Password:</strong> Admin123!</p>
            <p><em>You can change these after setup or create additional users.</em></p>
        </div>

        <form method="POST">
            <div class="setup-section">
                <h2>🔐 MySQL Configuration</h2>
                <p>Enter your MySQL root password (leave empty if using default XAMPP setup):</p>
                <input type="password" name="root_password" placeholder="MySQL root password (optional for XAMPP)">
                <br>
                <button type="submit">🚀 Setup All Databases</button>
            </div>
        </form>
        
        <div class="setup-section">
            <h2>📋 What This Setup Does</h2>
            <pre>
<strong>1. User Management Database (access_requests1):</strong>
   ✓ Creates database and grants access to 'Blaise' user
   ✓ Creates 'requests' table for user accounts
   ✓ Creates 'access_logs' table for activity tracking
   ✓ Inserts default admin account (Admin123/Admin123!)
   ✓ Inserts developer account (bswan/Admin123!)

<strong>2. Safety System Database (elevator_lockout_db):</strong>
   ✓ Creates database with root access
   ✓ Creates 'elevator_lockout' table for safety controls
   ✓ Inserts initial unlocked state
   ✓ Enables lockout/tagout functionality

<strong>3. Elevator Control Database (elevator):</strong>
   ✓ Creates database and grants access to 'ese' user
   ✓ Creates 'elevatorNetwork' table for movement tracking
   ✓ Inserts initial elevator position (floor 1)
   ✓ Enables elevator movement controls
            </pre>
        </div>
    </div>
        
    <?php else: ?>
        
    <div class="container">
        <h2>🔧 Running Complete Setup...</h2>
        
        <?php
        $root_password = $_POST['root_password'] ?? '';
        $setup_success = true;
        
        // Setup 1: access_requests1 database (User Management)
        echo "<h3>🔐 Setting up User Management Database...</h3>";
        try {
            $mysqli = new mysqli("localhost", "root", $root_password);
            if ($mysqli->connect_error) {
                throw new Exception("Root connection failed: " . $mysqli->connect_error);
            }
            
            echo "<div class='status success'>✅ Connected to MySQL as root</div>";
            
            // Hash the password Admin123!
            $hashed_password = password_hash('Admin123!', PASSWORD_DEFAULT);
            
            $access_sql = "
                CREATE DATABASE IF NOT EXISTS access_requests1;
                GRANT ALL PRIVILEGES ON access_requests1.* TO 'Blaise'@'localhost' IDENTIFIED BY 'Gitdead32!32';
                FLUSH PRIVILEGES;
            ";
            
            if ($mysqli->multi_query($access_sql)) {
                do {
                    if ($result = $mysqli->store_result()) {
                        $result->free();
                    }
                } while ($mysqli->next_result());
                echo "<div class='status success'>✅ access_requests1 database and user created</div>";
            } else {
                throw new Exception("Database creation failed: " . $mysqli->error);
            }
            
            $mysqli->close();
            
            // Connect as Blaise to create tables
            $user_mysqli = new mysqli("localhost", "Blaise", "Gitdead32!32", "access_requests1");
            if ($user_mysqli->connect_error) {
                throw new Exception("Blaise user connection failed: " . $user_mysqli->connect_error);
            }
            
            // Create tables
            $tables_sql = "
                CREATE TABLE IF NOT EXISTS requests (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(255) UNIQUE NOT NULL,
                    email VARCHAR(255) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    student_card VARCHAR(255) UNIQUE,
                    reason TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    approved BOOLEAN DEFAULT FALSE,
                    INDEX idx_username (username),
                    INDEX idx_email (email),
                    INDEX idx_student_card (student_card)
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
                echo "<div class='status success'>✅ User management tables created</div>";
            } else {
                throw new Exception("Table creation failed: " . $user_mysqli->error);
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
                $dev_card = 'BSWAN001',
                $dev_reason = 'Project developer account',
                $dev_approved = 1
            );
            $dev_stmt->execute();
            
            echo "<div class='status success'>✅ Default admin accounts created</div>";
            $user_mysqli->close();
            
        } catch (Exception $e) {
            echo "<div class='status error'>❌ User database setup failed: " . $e->getMessage() . "</div>";
            $setup_success = false;
        }
        
        // Setup 2: elevator_lockout_db database (Safety System)
        echo "<h3>🔒 Setting up Lockout Safety Database...</h3>";
        try {
            $mysqli = new mysqli("localhost", "root", $root_password);
            
            $lockout_sql = "
                CREATE DATABASE IF NOT EXISTS elevator_lockout_db;
                
                USE elevator_lockout_db;
                
                CREATE TABLE IF NOT EXISTS elevator_lockout (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    elevator_id INT DEFAULT 1,
                    is_locked_out BOOLEAN DEFAULT FALSE,
                    locked_by_user_id INT,
                    locked_by_username VARCHAR(255),
                    lockout_reason TEXT,
                    lockout_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    unlock_timestamp TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_elevator_lockout (elevator_id, is_locked_out)
                );
                
                INSERT IGNORE INTO elevator_lockout (elevator_id, is_locked_out, lockout_reason) 
                VALUES (1, FALSE, 'System initialized');
            ";
            
            if ($mysqli->multi_query($lockout_sql)) {
                do {
                    if ($result = $mysqli->store_result()) {
                        $result->free();
                    }
                } while ($mysqli->next_result());
                echo "<div class='status success'>✅ elevator_lockout_db database and tables created</div>";
            } else {
                throw new Exception("Lockout setup failed: " . $mysqli->error);
            }
            
            $mysqli->close();
            
        } catch (Exception $e) {
            echo "<div class='status error'>❌ Lockout database setup failed: " . $e->getMessage() . "</div>";
            $setup_success = false;
        }
        
        // Setup 3: elevator database (Movement Control)
        echo "<h3>🚁 Setting up Elevator Control Database...</h3>";
        try {
            $mysqli = new mysqli("localhost", "root", $root_password);
            
            $elevator_sql = "
                CREATE DATABASE IF NOT EXISTS elevator;
                GRANT ALL PRIVILEGES ON elevator.* TO 'ese'@'localhost' IDENTIFIED BY 'ese';
                FLUSH PRIVILEGES;
                
                USE elevator;
                
                CREATE TABLE IF NOT EXISTS elevatorNetwork (
                    nodeID INT PRIMARY KEY,
                    currentFloor INT DEFAULT 1
                );
                
                INSERT IGNORE INTO elevatorNetwork (nodeID, currentFloor) 
                VALUES (1, 1);
            ";
            
            if ($mysqli->multi_query($elevator_sql)) {
                do {
                    if ($result = $mysqli->store_result()) {
                        $result->free();
                    }
                } while ($mysqli->next_result());
                echo "<div class='status success'>✅ elevator database and tables created</div>";
            } else {
                throw new Exception("Elevator setup failed: " . $mysqli->error);
            }
            
            $mysqli->close();
            
        } catch (Exception $e) {
            echo "<div class='status error'>❌ Elevator database setup failed: " . $e->getMessage() . "</div>";
            $setup_success = false;
        }
        
        // Final verification
        if ($setup_success) {
            echo "<div class='setup-section'>";
            echo "<h3>🎉 Complete Setup Successful!</h3>";
            echo "<div class='credentials'>";
            echo "<h4>🔑 Login Information:</h4>";
            echo "<p><strong>Username:</strong> Admin123</p>";
            echo "<p><strong>Password:</strong> Admin123!</p>";
            echo "</div>";
            echo "<p><strong>🚀 Your elevator system is now ready! Try these links:</strong></p>";
            echo "<ul>";
            echo "<li><a href='login1.php' style='color: #007bff; font-weight: bold;'>🔐 Login to System</a></li>";
            echo "<li><a href='request_access.php' style='color: #007bff; font-weight: bold;'>👤 Register New User</a></li>";
            echo "<li><a href='admin_lockout.php' style='color: #007bff; font-weight: bold;'>🔒 Lockout Control Panel</a></li>";
            echo "<li><a href='index.php' style='color: #007bff; font-weight: bold;'>🚁 Main Elevator Interface</a></li>";
            echo "<li><a href='test_elevator.html' style='color: #007bff; font-weight: bold;'>🧪 Test Interface</a></li>";
            echo "</ul>";
            echo "</div>";
        } else {
            echo "<div class='status error'>";
            echo "<h3>❌ Setup Incomplete</h3>";
            echo "<p>Some databases failed to set up properly. Please check the errors above and try again.</p>";
            echo "<p><strong>Troubleshooting:</strong></p>";
            echo "<ul>";
            echo "<li>Make sure XAMPP MySQL is running</li>";
            echo "<li>Check if you can access phpMyAdmin</li>";
            echo "<li>Verify MySQL root password (usually empty in XAMPP)</li>";
            echo "<li>Try running individual setup scripts manually</li>";
            echo "</ul>";
            echo "</div>";
        }
        ?>
    </div>
        
    <?php endif; ?>
    
    <div class="container">
        <div class="setup-section">
            <h3>🔗 Related Files & Documentation</h3>
            <ul>
                <li><strong>Manual Setup Scripts:</strong> <code>sql/setup_access_requests.sql</code>, <code>sql/simple_setup.sql</code></li>
                <li><strong>Documentation:</strong> <code>README.md</code></li>
                <li><strong>Database Check:</strong> <code>check_database_structure.php</code></li>
                <li><strong>Individual Setup:</strong> <code>setup_lockout_db.php</code></li>
            </ul>
        </div>
    </div>
</body>
</html>
