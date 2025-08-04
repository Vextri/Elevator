<?php
// setup_database.php - Automatic database setup script
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elevator Lockout Database Setup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        .success { color: #155724; background: #d4edda; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .error { color: #721c24; background: #f8d7da; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .warning { color: #856404; background: #fff3cd; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .info { color: #0c5460; background: #d1ecf1; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .step { background: #f8f9fa; padding: 15px; margin: 10px 0; border-left: 4px solid #007bff; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #0056b3; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 300px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h1> Elevator Lockout Database Setup</h1>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $root_password = $_POST['root_password'] ?? '';
            
            echo "<div class='info'><strong>Setting up database...</strong></div>";
            
            try {
                // Connect as root to create database and user
                $root_pdo = new PDO('mysql:host=localhost', 'root', $root_password);
                $root_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                echo "<div class='success'>✅ Connected to MySQL as root</div>";
                
                // Create database
                $root_pdo->exec("CREATE DATABASE IF NOT EXISTS elevator_lockout_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                echo "<div class='success'>✅ Database 'elevator_lockout_db' created</div>";
                
                // Grant privileges to Blaise user
                //This is a password I made up for the sake of the project it is not confidential
                $root_pdo->exec("GRANT ALL PRIVILEGES ON elevator_lockout_db.* TO 'Blaise'@'localhost' IDENTIFIED BY 'Gitdead32!32'");
                $root_pdo->exec("GRANT ALL PRIVILEGES ON elevator_lockout_db.* TO 'Blaise'@'127.0.0.1' IDENTIFIED BY 'Gitdead32!32'");
                $root_pdo->exec("FLUSH PRIVILEGES");
                echo "<div class='success'>✅ Granted privileges to user 'Blaise'</div>";
                
                // Now connect as Blaise to create tables
                //This is a password I made up for the sake of the project it is not confidential
                $pdo = new PDO('mysql:host=localhost;dbname=elevator_lockout_db', 'Blaise', 'Gitdead32!32');
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                echo "<div class='success'>✅ Connected as 'Blaise' user</div>";
                
                // Create lockout table
                $pdo->exec("CREATE TABLE IF NOT EXISTS elevator_lockout (
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
                )");
                echo "<div class='success'>✅ Created 'elevator_lockout' table</div>";
                
                // Create users table
                $pdo->exec("CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(255) UNIQUE NOT NULL,
                    email VARCHAR(255) UNIQUE NOT NULL,
                    password_hash VARCHAR(255) NOT NULL,
                    role ENUM('user', 'admin') DEFAULT 'user',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");
                echo "<div class='success'>✅ Created 'users' table</div>";
                
                // Insert default admin user
                $pdo->exec("INSERT IGNORE INTO users (username, email, password_hash, role) 
                           VALUES ('admin', 'admin@elevator.local', '$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')");
                echo "<div class='success'>✅ Created default admin user (username: admin, password: admin123)</div>";
                
                // Insert initial lockout record
                $pdo->exec("INSERT IGNORE INTO elevator_lockout (elevator_id, is_locked_out, lockout_reason) 
                           VALUES (1, FALSE, 'System initialized')");
                echo "<div class='success'>✅ Created initial lockout record</div>";
                
                // Verify setup
                $stmt = $pdo->query("SELECT COUNT(*) FROM elevator_lockout");
                $lockout_count = $stmt->fetchColumn();
                
                $stmt = $pdo->query("SELECT COUNT(*) FROM users");
                $user_count = $stmt->fetchColumn();
                
                echo "<div class='success'><strong>🎉 Database setup completed successfully!</strong><br>";
                echo "• Lockout records: $lockout_count<br>";
                echo "• Users: $user_count<br>";
                echo "• Database: elevator_lockout_db<br>";
                echo "• User: Blaise</div>";
                
                echo "<div class='info'><strong>Next Steps:</strong><br>";
                echo "1. Visit <a href='test_lockout_database.php'>test_lockout_database.php</a> to verify<br>";
                echo "2. Visit <a href='admin_lockout.php'>admin_lockout.php</a> to manage lockouts<br>";
                echo "3. Test the elevator interfaces</div>";
                
            } catch (PDOException $e) {
                echo "<div class='error'>❌ Database setup failed!<br>";
                echo "<strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
                
                echo "<div class='warning'><strong>Common Issues:</strong><br>";
                echo "• Check your root password<br>";
                echo "• Make sure MySQL is running<br>";
                echo "• Try using an empty password if you haven't set one</div>";
            }
        } else {
            ?>
            <div class='info'>
                <strong>This script will automatically:</strong><br>
                • Create the 'elevator_lockout_db' database<br>
                • Set up the 'Blaise' user with proper permissions<br>
                • Create all required tables<br>
                • Insert default data
            </div>
            
            <div class='step'>
                <strong>Step 1:</strong> Make sure XAMPP MySQL is running
            </div>
            
            <div class='step'>
                <strong>Step 2:</strong> Enter your MySQL root password below (usually empty for XAMPP)
            </div>
            
            <form method="POST">
                <div class='form-group'>
                    <label for="root_password">MySQL Root Password:</label>
                    <input type="password" id="root_password" name="root_password" placeholder="Leave empty if no password set">
                </div>
                <button type="submit">🚀 Setup Database</button>
            </form>
            
            <div class='warning'>
                <strong>Note:</strong> If you're using XAMPP, the root password is usually empty (just click Setup Database without entering anything).
            </div>
            <?php
        }
        ?>
    </div>
</body>
</html>
