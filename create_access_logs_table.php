<?php
// create_access_logs_table.php - Quick fix to create missing access_logs table
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Access Logs Table</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: green; background: #d4edda; padding: 15px; margin: 10px 0; border-radius: 5px; border: 1px solid #c3e6cb; }
        .error { color: red; background: #f8d7da; padding: 15px; margin: 10px 0; border-radius: 5px; border: 1px solid #f5c6cb; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
        .btn:hover { background: #0056b3; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Create Missing Access Logs Table</h1>
        
        <?php
        if (isset($_GET['create']) && $_GET['create'] === 'yes') {
            try {
                $mysqli = new mysqli("localhost", "root", "", "elevator_lockout_db");
                
                if ($mysqli->connect_error) {
                    throw new Exception("Connection failed: " . $mysqli->connect_error);
                }
                
                echo "<div class='success'>✅ Connected to elevator_lockout_db</div>";
                
                // Create access_logs table
                $sql = "CREATE TABLE access_logs (
                    log_id INT AUTO_INCREMENT PRIMARY KEY,
                    student_card VARCHAR(255),
                    access_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    success BOOLEAN DEFAULT TRUE,
                    reason TEXT,
                    ip_address VARCHAR(45),
                    user_agent TEXT
                )";
                
                if ($mysqli->query($sql) === TRUE) {
                    echo "<div class='success'>";
                    echo "<h2>🎉 Success!</h2>";
                    echo "<p><strong>access_logs table created successfully!</strong></p>";
                    echo "<ul>";
                    echo "<li>✅ Table structure created</li>";
                    echo "<li>✅ Ready for logging lockout activities</li>";
                    echo "<li>✅ Can now enable full logging in admin_lockout.php</li>";
                    echo "</ul>";
                    echo "</div>";
                    
                    // Verify table was created
                    $check = $mysqli->query("SHOW TABLES LIKE 'access_logs'");
                    if ($check->num_rows > 0) {
                        echo "<div class='success'>✅ Verification: access_logs table exists!</div>";
                        
                        // Show table structure
                        echo "<h3>📋 Table Structure:</h3>";
                        $structure = $mysqli->query("DESCRIBE access_logs");
                        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                        echo "<tr style='background: #f8f9fa;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
                        while ($field = $structure->fetch_array()) {
                            echo "<tr>";
                            echo "<td>" . $field['Field'] . "</td>";
                            echo "<td>" . $field['Type'] . "</td>";
                            echo "<td>" . $field['Null'] . "</td>";
                            echo "<td>" . $field['Key'] . "</td>";
                            echo "<td>" . $field['Default'] . "</td>";
                            echo "<td>" . $field['Extra'] . "</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                    }
                    
                    echo "<div class='success'>";
                    echo "<h3>🚀 Next Steps:</h3>";
                    echo "<ol>";
                    echo "<li><strong>Go back to admin_lockout.php</strong> - The logging will now work</li>";
                    echo "<li><strong>Test lockout/unlock</strong> - Activities will be logged</li>";
                    echo "<li><strong>Check the logs</strong> - View activity history</li>";
                    echo "</ol>";
                    echo "<p><a href='admin_lockout.php' class='btn'>Go to Lockout Control Panel</a></p>";
                    echo "<p><a href='check_lockout_tables.php' class='btn'>Verify All Tables</a></p>";
                    echo "</div>";
                    
                } else {
                    throw new Exception("Error creating table: " . $mysqli->error);
                }
                
                $mysqli->close();
                
            } catch (Exception $e) {
                echo "<div class='error'><strong>❌ Error:</strong><br>" . $e->getMessage() . "</div>";
            }
            
        } else {
            // Show what will be created
            echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
            echo "<h3>📋 What This Will Create:</h3>";
            echo "<p>The <code>access_logs</code> table will store:</p>";
            echo "<ul>";
            echo "<li><strong>log_id</strong> - Unique ID for each log entry</li>";
            echo "<li><strong>student_card</strong> - User identifier</li>";
            echo "<li><strong>access_time</strong> - When the action occurred</li>";
            echo "<li><strong>success</strong> - Whether the action succeeded</li>";
            echo "<li><strong>reason</strong> - Description of the action</li>";
            echo "<li><strong>ip_address</strong> - User's IP address</li>";
            echo "<li><strong>user_agent</strong> - Browser information</li>";
            echo "</ul>";
            echo "</div>";
            
            echo "<h3>📝 SQL Command:</h3>";
            echo "<pre>";
            echo "CREATE TABLE access_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    student_card VARCHAR(255),
    access_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    success BOOLEAN DEFAULT TRUE,
    reason TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT
);";
            echo "</pre>";
            
            echo "<h3>🚀 Ready to Create?</h3>";
            echo "<p><a href='?create=yes' class='btn'>✅ Create access_logs Table</a></p>";
            echo "<p><a href='check_lockout_tables.php' class='btn'>🔍 Check Current Tables</a></p>";
        }
        ?>
    </div>
</body>
</html>
