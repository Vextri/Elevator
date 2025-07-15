<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elevator System - Setup & Control Center</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 20px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            background: #f8f9fa;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .card h3 {
            margin-top: 0;
            color: #343a40;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        button, .button {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        button:hover, .button:hover {
            background: linear-gradient(45deg, #0056b3, #004085);
            transform: translateY(-2px);
        }
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .status-unknown { background: #6c757d; }
        .status-good { background: #28a745; }
        .status-warning { background: #ffc107; }
        .status-error { background: #dc3545; }
        .setup-card { border-left: 4px solid #28a745; }
        .control-card { border-left: 4px solid #007bff; }
        .maintenance-card { border-left: 4px solid #ffc107; }
        .info-card { border-left: 4px solid #17a2b8; }
        .emergency-section {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .alert {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚁 Elevator Control System</h1>
        <p>Complete Setup, Control & Maintenance Center</p>
    </div>

    <?php
    // Quick system status check
    $mysql_status = false;
    $database_status = false;
    
    try {
        $mysqli = new mysqli("localhost", "root", "");
        if (!$mysqli->connect_error) {
            $mysql_status = true;
            
            // Check if databases exist
            $db_check = $mysqli->query("SHOW DATABASES LIKE 'access_requests1'");
            if ($db_check && $db_check->num_rows > 0) {
                $database_status = true;
            }
        }
    } catch (Exception $e) {
        // Try with common passwords
        $passwords = ['root', 'admin', 'password', 'mysql'];
        foreach ($passwords as $pwd) {
            try {
                $mysqli = new mysqli("localhost", "root", $pwd);
                if (!$mysqli->connect_error) {
                    $mysql_status = true;
                    break;
                }
            } catch (Exception $e) {
                continue;
            }
        }
    }
    ?>

    <div class="container">
        <div class="alert">
            <h3>📊 System Status</h3>
            <p>
                <span class="status-indicator <?php echo $mysql_status ? 'status-good' : 'status-error'; ?>"></span>
                MySQL Connection: <?php echo $mysql_status ? 'Connected' : 'Failed'; ?>
            </p>
            <p>
                <span class="status-indicator <?php echo $database_status ? 'status-good' : 'status-warning'; ?>"></span>
                Databases: <?php echo $database_status ? 'Configured' : 'Need Setup'; ?>
            </p>
            
            <?php if (!$mysql_status): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;">
                    <strong>⚠️ MySQL Connection Issue Detected!</strong><br>
                    Start with the diagnostic tools below or run smart setup.
                </div>
            <?php elseif (!$database_status): ?>
                <div style="background: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin: 10px 0;">
                    <strong>💡 Setup Required!</strong><br>
                    Your MySQL is working but databases need to be created.
                </div>
            <?php else: ?>
                <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;">
                    <strong>✅ System Ready!</strong><br>
                    You can now use the elevator control system.
                </div>
            <?php endif; ?>
        </div>

        <div class="grid">
            <!-- Setup Section -->
            <div class="card setup-card">
                <h3>🚀 Setup & Installation</h3>
                <p>First-time setup and database configuration tools.</p>
                
                <a href="smart_setup.php" class="button">🤖 Smart Auto-Setup</a>
                <a href="setup_all_databases.php" class="button">⚙️ Manual Setup</a>
                <a href="diagnose_mysql.php" class="button">🔍 Diagnose MySQL</a>
                
                <hr style="margin: 15px 0;">
                <small><strong>Quick Start:</strong> Use Smart Auto-Setup for easiest installation</small>
            </div>

            <!-- Control Section -->
            <div class="card control-card">
                <h3>🎮 Elevator Control</h3>
                <p>Main elevator interfaces and control panels.</p>
                
                <a href="login1.php" class="button">🔑 Login System</a>
                <a href="index.php" class="button">🚁 Inside Control</a>
                <a href="outside.php" class="button">🏢 Outside Control</a>
                <a href="test_elevator.html" class="button">🧪 Test Interface</a>
                
                <hr style="margin: 15px 0;">
                <small><strong>Default Login:</strong> Admin123 / Admin123!</small>
            </div>

            <!-- Safety Section -->
            <div class="card maintenance-card">
                <h3>🔒 Safety & Lockout</h3>
                <p>Lockout/Tagout (LOTO) safety system for maintenance.</p>
                
                <a href="admin_lockout.php" class="button">🔐 Lockout Control</a>
                <a href="test_lockout_integration.html" class="button">🛡️ Test Safety System</a>
                
                <hr style="margin: 15px 0;">
                <small><strong>Safety First:</strong> Always use LOTO before maintenance</small>
            </div>

            <!-- User Management -->
            <div class="card info-card">
                <h3>👥 User Management</h3>
                <p>User registration, approval, and access control.</p>
                
                <a href="request_access.php" class="button">📝 Request Access</a>
                <a href="user_requests.php" class="button">📋 View Requests</a>
                <a href="approve_user.php" class="button">✅ Approve Users</a>
                
                <hr style="margin: 15px 0;">
                <small><strong>Note:</strong> Admin approval required for new users</small>
            </div>

            <!-- Testing & Diagnostics -->
            <div class="card maintenance-card">
                <h3>🔧 Testing & Diagnostics</h3>
                <p>System testing, validation, and troubleshooting tools.</p>
                
                <a href="check_database_structure.php" class="button">📊 Database Check</a>
                <a href="test_elevator_api.php" class="button">🔗 Test API</a>
                <a href="test_card_login.html" class="button">💳 Test Card Reader</a>
                
                <hr style="margin: 15px 0;">
                <small><strong>Tip:</strong> Run diagnostics if you encounter issues</small>
            </div>

            <!-- Documentation -->
            <div class="card info-card">
                <h3>📚 Documentation</h3>
                <p>Project information, guides, and technical details.</p>
                
                <a href="README.md" class="button">📖 Full Documentation</a>
                <a href="deliverables.html" class="button">📋 Project Details</a>
                <a href="debug_status.html" class="button">🐛 Debug Info</a>
                
                <hr style="margin: 15px 0;">
                <small><strong>Resources:</strong> Complete setup and usage guides</small>
            </div>
        </div>

        <?php if (!$mysql_status): ?>
        <div class="emergency-section">
            <h3>🚨 Emergency MySQL Recovery</h3>
            <p>If you can't connect to MySQL at all, try these emergency fixes:</p>
            
            <div style="margin: 10px 0;">
                <strong>Windows Users:</strong>
                <a href="reset_mysql_password.bat" class="button" download>💾 Download Password Reset Tool</a>
                <small>(Run as Administrator)</small>
            </div>
            
            <div style="margin: 10px 0;">
                <strong>Alternative:</strong>
                <a href="http://localhost/phpmyadmin" target="_blank" class="button">🗃️ Try phpMyAdmin</a>
                <small>(If this works, you can setup manually)</small>
            </div>
            
            <details style="margin: 10px 0;">
                <summary style="cursor: pointer; font-weight: bold;">📋 Manual Command Line Fix</summary>
                <div style="background: #f8f9fa; padding: 10px; margin: 10px 0; border-radius: 5px; font-family: monospace;">
                    1. Stop XAMPP MySQL<br>
                    2. Run: <code>C:\xampp\mysql\bin\mysqld --skip-grant-tables</code><br>
                    3. In new window: <code>C:\xampp\mysql\bin\mysql -u root</code><br>
                    4. Execute: <code>UPDATE mysql.user SET Password='' WHERE User='root';</code><br>
                    5. Execute: <code>FLUSH PRIVILEGES;</code><br>
                    6. Restart XAMPP MySQL
                </div>
            </details>
        </div>
        <?php endif; ?>

        <div class="container" style="text-align: center; margin-top: 30px;">
            <h3>🎯 Quick Actions</h3>
            <p>Most common tasks for getting started:</p>
            
            <?php if (!$database_status): ?>
                <a href="smart_setup.php" class="button" style="font-size: 18px; padding: 15px 30px;">🚀 Start Here: Run Setup</a>
            <?php else: ?>
                <a href="login1.php" class="button" style="font-size: 18px; padding: 15px 30px;">🔑 Login to System</a>
                <a href="index.php" class="button" style="font-size: 18px; padding: 15px 30px;">🚁 Control Elevator</a>
            <?php endif; ?>
        </div>
    </div>

    <div style="text-align: center; color: white; margin: 20px 0;">
        <p>🔧 <strong>Need Help?</strong> Check the <a href="README.md" style="color: #ffc107;">full documentation</a> or run the <a href="diagnose_mysql.php" style="color: #ffc107;">diagnostic tool</a></p>
    </div>
</body>
</html>
