<?php
// setup_lockout_db.php - One-click setup for lockout database
?>
<!DOCTYPE html>
<html>
<head>
    <title>Lockout Database Setup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: green; background: #d4edda; padding: 15px; margin: 10px 0; border-radius: 5px; border: 1px solid #c3e6cb; }
        .error { color: red; background: #f8d7da; padding: 15px; margin: 10px 0; border-radius: 5px; border: 1px solid #f5c6cb; }
        .warning { color: orange; background: #fff3cd; padding: 15px; margin: 10px 0; border-radius: 5px; border: 1px solid #ffeaa7; }
        .info { color: blue; background: #d1ecf1; padding: 15px; margin: 10px 0; border-radius: 5px; border: 1px solid #bee5eb; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f8f9fa; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #1e7e34; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .step { background: #e9ecef; padding: 10px; margin: 10px 0; border-left: 4px solid #007bff; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Elevator Lockout Database Setup</h1>
        
        <?php
        $setup_mode = $_GET['setup'] ?? '';
        
        if ($setup_mode === 'run') {
            echo "<div class='info'><strong>🔧 Running Database Setup...</strong></div>";
            
            try {
                // First, try to connect to MySQL to create database
                //This is a password I made up for the sake of the project it is not confidential
                $conn = new mysqli("localhost", "Blaise", "Gitdead32!32");
                
                if ($conn->connect_error) {
                    throw new Exception("Connection failed: " . $conn->connect_error);
                }
                
                echo "<div class='success'>✅ Connected to MySQL server</div>";
                
                // Create database if it doesn't exist
                $sql = "CREATE DATABASE IF NOT EXISTS elevator_lockout_db";
                if ($conn->query($sql) === TRUE) {
                    echo "<div class='success'>✅ Database 'elevator_lockout_db' created/verified</div>";
                } else {
                    throw new Exception("Error creating database: " . $conn->error);
                }
                
                $conn->close();
                
                // Now connect to the specific database and create tables
                //This is a password I made up for the sake of the project it is not confidential
                $db = new mysqli("localhost", "Blaise", "Gitdead32!32", "elevator_lockout_db");
                
                if ($db->connect_error) {
                    throw new Exception("Connection to lockout database failed: " . $db->connect_error);
                }
                
                echo "<div class='success'>✅ Connected to elevator_lockout_db</div>";
                
                // Read and execute the SQL setup file
                $sql_file = 'sql/simple_setup.sql';
                if (!file_exists($sql_file)) {
                    throw new Exception("SQL file not found: $sql_file");
                }
                
                $sql_content = file_get_contents($sql_file);
                
                // Split by semicolons and execute each statement
                $statements = explode(';', $sql_content);
                
                foreach ($statements as $statement) {
                    $statement = trim($statement);
                    if (empty($statement) || stripos($statement, 'USE ') === 0) {
                        continue; // Skip empty statements and USE statements
                    }
                    
                    if ($db->query($statement) === FALSE) {
                        // Only throw error if it's not a "table already exists" error
                        if (!str_contains($db->error, 'already exists')) {
                            throw new Exception("Error executing SQL: " . $db->error . "\nStatement: " . $statement);
                        } else {
                            echo "<div class='warning'>⚠️ Table already exists (skipping): " . substr($statement, 0, 50) . "...</div>";
                        }
                    } else {
                        if (stripos($statement, 'CREATE TABLE') === 0) {
                            echo "<div class='success'>✅ Created table successfully</div>";
                        } elseif (stripos($statement, 'INSERT') === 0) {
                            echo "<div class='success'>✅ Inserted initial data</div>";
                        }
                    }
                }
                
                // Verify tables were created
                echo "<h3>📋 Verifying Tables Created:</h3>";
                $result = $db->query("SHOW TABLES");
                echo "<table>";
                echo "<tr><th>Table Name</th></tr>";
                while ($row = $result->fetch_array()) {
                    echo "<tr><td>" . $row[0] . "</td></tr>";
                }
                echo "</table>";
                
                // Show current lockout status
                echo "<h3>🔒 Current Lockout Status:</h3>";
                $result = $db->query("SELECT * FROM elevator_lockout ORDER BY id DESC LIMIT 1");
                if ($result && $result->num_rows > 0) {
                    $lockout = $result->fetch_assoc();
                    echo "<table>";
                    echo "<tr><th>Field</th><th>Value</th></tr>";
                    foreach ($lockout as $key => $value) {
                        echo "<tr><td>$key</td><td>" . ($value ?? 'NULL') . "</td></tr>";
                    }
                    echo "</table>";
                    
                    if ($lockout['is_locked_out']) {
                        echo "<div class='error'>🔒 <strong>ELEVATOR IS CURRENTLY LOCKED OUT</strong></div>";
                    } else {
                        echo "<div class='success'>🔓 <strong>ELEVATOR IS OPERATIONAL</strong></div>";
                    }
                } else {
                    echo "<div class='error'>❌ No lockout records found!</div>";
                }
                
                echo "<div class='success'><h2>🎉 Setup Complete!</h2>";
                echo "<p>Your lockout database is ready. You can now:</p>";
                echo "<ul>";
                echo "<li><a href='admin_lockout.php' class='btn btn-success'>Go to Lockout Control Panel</a></li>";
                echo "<li><a href='index.php' class='btn'>Test Elevator Interface</a></li>";
                echo "<li><a href='check_database_structure.php' class='btn'>Check Database Structure</a></li>";
                echo "</ul></div>";
                
                $db->close();
                
            } catch (Exception $e) {
                echo "<div class='error'><strong>❌ Setup Failed:</strong><br>" . $e->getMessage() . "</div>";
                echo "<div class='info'><strong>💡 Troubleshooting:</strong><br>";
                echo "1. Make sure XAMPP MySQL is running<br>";
                echo "2. Check your database credentials in the script<br>";
                echo "3. Ensure you have permission to create databases<br>";
                echo "4. Try running the SQL manually in phpMyAdmin</div>";
            }
            
        } else {
            // Show setup instructions
            echo "<div class='info'>";
            echo "<h2>📋 Pre-Setup Checklist:</h2>";
            echo "<ol>";
            echo "<li>✅ XAMPP is running (Apache + MySQL)</li>";
            echo "<li>✅ You have the database credentials (Username: Blaise)</li>";
            echo "<li>✅ Your existing user database 'access_requests1' is working</li>";
            echo "</ol>";
            echo "</div>";
            
            echo "<div class='step'>";
            echo "<h3>🎯 What This Setup Will Do:</h3>";
            echo "<ol>";
            echo "<li>Create database: <code>elevator_lockout_db</code></li>";
            echo "<li>Create table: <code>elevator_lockout</code> (stores lockout status)</li>";
            echo "<li>Create table: <code>access_logs</code> (stores activity logs)</li>";
            echo "<li>Insert initial data (elevator unlocked)</li>";
            echo "<li>Verify everything is working</li>";
            echo "</ol>";
            echo "</div>";
            
            echo "<div class='warning'>";
            echo "<h3>⚠️ Important Notes:</h3>";
            echo "<ul>";
            echo "<li>This will use your existing user system in 'access_requests1'</li>";
            echo "<li>No user data will be modified or duplicated</li>";
            echo "<li>If tables already exist, they will be preserved</li>";
            echo "<li>You can run this setup multiple times safely</li>";
            echo "</ul>";
            echo "</div>";
            
            echo "<h3>🚀 Ready to Setup?</h3>";
            echo "<a href='?setup=run' class='btn btn-success'>Run Database Setup</a>";
            echo "<a href='check_database_structure.php' class='btn'>Check Current Database Status</a>";
            
            echo "<h3>📝 Manual Setup (Alternative):</h3>";
            echo "<div class='step'>";
            echo "If the automatic setup fails, you can run this SQL manually in phpMyAdmin:";
            echo "<pre>";
            echo htmlspecialchars(file_get_contents('sql/simple_setup.sql'));
            echo "</pre>";
            echo "</div>";
        }
        ?>
        
        <hr>
        <p><small>💡 <strong>Need Help?</strong> Check the <a href='../README.md'>README.md</a> or <a href='check_database_structure.php'>Database Structure</a></small></p>
    </div>
</body>
</html>
