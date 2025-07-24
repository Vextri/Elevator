<?php
// debug_request_access.php - Diagnose and fix the request_access.php duplicate entry issue

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database credentials - try different users
$credentials = [
    ['username' => 'Blaise', 'password' => 'Gitdead32!32'],
    ['username' => 'root', 'password' => ''],
    ['username' => 'root', 'password' => 'ese'],
    ['username' => 'ese', 'password' => 'ese']
];

$servername = "localhost";
$dbname = "access_requests1";

function testConnection($servername, $username, $password, $dbname) {
    try {
        // Suppress errors and use exception handling instead
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conn = new mysqli($servername, $username, $password, $dbname);
        return $conn;
    } catch (mysqli_sql_exception $e) {
        // Return false if connection fails
        return false;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Access Debug Tool</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            max-width: 1200px; 
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
        pre { 
            background: #f8f9fa; 
            padding: 15px; 
            border-radius: 8px; 
            overflow-x: auto; 
            border: 1px solid #e9ecef;
            font-size: 12px;
        }
        button { 
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white; 
            padding: 12px 25px; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            font-size: 14px;
            margin: 5px;
        }
        .section { 
            border: 1px solid #ddd; 
            margin: 20px 0; 
            padding: 20px; 
            border-radius: 8px; 
            background: #f8f9fa;
        }
        h1 { color: #2c3e50; text-align: center; }
        h2 { color: #495057; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .form-section {
            background: #e7f3ff;
            border: 2px solid #007bff;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 5px 0;
            font-size: 14px;
        }
        .test-result {
            background: #f8f9fa;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Request Access Debug Tool</h1>
        <p style="text-align: center; color: #6c757d;">
            Diagnose and fix the "Duplicate entry '' for key 'PRIMARY'" error
        </p>
    </div>

    <div class="container">
        <div class="section">
            <h2>📊 Database Connection Test</h2>
            <?php
            $working_conn = null;
            $working_creds = null;

            foreach ($credentials as $cred) {
                try {
                    $conn = testConnection($servername, $cred['username'], $cred['password'], $dbname);
                    if ($conn) {
                        echo "<div class='status success'>✅ Connection successful with user: {$cred['username']}</div>";
                        if (!$working_conn) {
                            $working_conn = $conn;
                            $working_creds = $cred;
                        }
                    } else {
                        echo "<div class='status error'>❌ Connection failed with user: {$cred['username']} - Invalid credentials or access denied</div>";
                    }
                } catch (Exception $e) {
                    echo "<div class='status error'>❌ Connection failed with user: {$cred['username']} - " . $e->getMessage() . "</div>";
                }
            }

            if (!$working_conn) {
                echo "<div class='status error'>❌ No working database connection found!</div>";
                echo "<div class='info'><strong>Troubleshooting Steps:</strong><br>";
                echo "1. Check if MySQL/XAMPP is running<br>";
                echo "2. Verify database credentials<br>";
                echo "3. Check if database 'access_requests1' exists<br>";
                echo "4. Try running setup scripts first<br>";
                echo "5. Use <code>quick_mysql_test.php</code> or <code>diagnose_mysql.php</code> for more detailed diagnostics</div>";
                echo "<div class='info'><strong>Common Solutions:</strong><br>";
                echo "• If root password is 'ese': Make sure XAMPP MySQL is configured correctly<br>";
                echo "• If root password is empty: This is the default XAMPP configuration<br>";
                echo "• If using 'Blaise' user: Make sure this user exists and has proper privileges<br>";
                echo "• If database doesn't exist: Run <code>setup_all_databases.php</code> first</div>";
                exit;
            }
            ?>
        </div>

        <div class="section">
            <h2>🗃️ Database Structure Analysis</h2>
            <?php
            // Check if database exists
            try {
                $db_check = new mysqli($servername, $working_creds['username'], $working_creds['password']);
                $result = $db_check->query("SHOW DATABASES LIKE 'access_requests1'");
                
                if ($result->num_rows == 0) {
                    echo "<div class='status warning'>⚠️ Database 'access_requests1' does not exist!</div>";
                    echo "<div class='info'>Please run the setup scripts first: <code>setup_all_databases.php</code> or <code>smart_setup.php</code></div>";
                } else {
                    echo "<div class='status success'>✅ Database 'access_requests1' exists</div>";
                    
                    // Check table structure
                    $result = $working_conn->query("DESCRIBE requests");
                    if ($result) {
                        echo "<h3>📋 Table Structure:</h3>";
                        echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
                        while ($row = $result->fetch_assoc()) {
                            $highlight = '';
                            // Highlight important fields
                            if ($row['Field'] == 'id' && $row['Extra'] == 'auto_increment') {
                                $highlight = 'style="background-color: #d4edda;"'; // Green for proper auto-increment
                            } elseif ($row['Field'] == 'fullname') {
                                $highlight = 'style="background-color: #e7f3ff;"'; // Blue for fullname field
                            }
                            echo "<tr $highlight>";
                            foreach ($row as $value) {
                                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                            }
                            echo "</tr>";
                        }
                        echo "</table>";
                        
                        // Check if fullname field exists
                        $fullname_exists = false;
                        $id_auto_increment = false;
                        $result->data_seek(0); // Reset result pointer
                        while ($row = $result->fetch_assoc()) {
                            if ($row['Field'] == 'fullname') {
                                $fullname_exists = true;
                            }
                            if ($row['Field'] == 'id' && $row['Extra'] == 'auto_increment') {
                                $id_auto_increment = true;
                            }
                        }
                        
                        if (!$fullname_exists) {
                            echo "<div class='status error'>❌ Missing 'fullname' field! This will cause the form submission to fail.</div>";
                        } else {
                            echo "<div class='status success'>✅ 'fullname' field exists</div>";
                        }
                        
                        if (!$id_auto_increment) {
                            echo "<div class='status error'>❌ 'id' field is not set to AUTO_INCREMENT! This may cause primary key issues.</div>";
                        } else {
                            echo "<div class='status success'>✅ 'id' field is properly configured as AUTO_INCREMENT</div>";
                        }
                        
                    } else {
                        echo "<div class='status error'>❌ Table 'requests' does not exist!</div>";
                        echo "<div class='info'>Error: " . $working_conn->error . "</div>";
                        echo "<div class='info'>You need to create the table first. Use the 'Fix Table Structure' button below.</div>";
                    }
                    
                    // Check existing data (only if table exists)
                    if ($result) {
                        $result = $working_conn->query("SELECT * FROM requests ORDER BY id DESC LIMIT 10");
                        if ($result) {
                            echo "<h3>📝 Recent Records (Last 10):</h3>";
                            if ($result->num_rows > 0) {
                                echo "<table><tr><th>ID</th><th>Full Name</th><th>Username</th><th>Email</th><th>Student Card</th><th>Reason</th><th>Created At</th><th>Approved</th></tr>";
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['id'] ?? '') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['fullname'] ?? 'N/A') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['username'] ?? '') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['email'] ?? '') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['student_card'] ?? 'N/A') . "</td>";
                                    echo "<td>" . htmlspecialchars(substr($row['reason'] ?? '', 0, 50)) . "...</td>";
                                    echo "<td>" . htmlspecialchars($row['created_at'] ?? '') . "</td>";
                                    echo "<td>" . ($row['approved'] ? 'Yes' : 'No') . "</td>";
                                    echo "</tr>";
                                }
                                echo "</table>";
                            } else {
                                echo "<div class='info'>No records found in the table.</div>";
                            }
                            
                            // Check for problematic records
                            $result = $working_conn->query("SELECT COUNT(*) as count FROM requests WHERE id = '' OR id IS NULL");
                            if ($result) {
                                $row = $result->fetch_assoc();
                                if ($row['count'] > 0) {
                                    echo "<div class='status error'>❌ Found {$row['count']} records with empty or NULL id!</div>";
                                } else {
                                    echo "<div class='status success'>✅ No problematic id values found</div>";
                                }
                            }
                        }
                    }
                }
                $db_check->close();
            } catch (Exception $e) {
                echo "<div class='status error'>❌ Error analyzing database: " . $e->getMessage() . "</div>";
            }
            ?>
        </div>

        <div class="section">
            <h2>🔧 Fix Table Structure</h2>
            <?php if (isset($_POST['fix_table'])): ?>
                <?php
                // Drop and recreate the table with correct structure
                $fix_sql = "
                    DROP TABLE IF EXISTS requests;
                    CREATE TABLE requests (
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
                ";
                
                if ($working_conn->multi_query($fix_sql)) {
                    // Clear any remaining results
                    while ($working_conn->next_result()) {
                        if ($res = $working_conn->store_result()) {
                            $res->free();
                        }
                    }
                    echo "<div class='status success'>✅ Table structure fixed!</div>";
                } else {
                    echo "<div class='status error'>❌ Error fixing table: " . $working_conn->error . "</div>";
                }
                ?>
            <?php else: ?>
                <form method="post">
                    <p>If the table structure is incorrect, click this button to drop and recreate it:</p>
                    <button type="submit" name="fix_table" onclick="return confirm('This will delete all existing data in the requests table. Continue?')">
                        🔧 Fix Table Structure
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <div class="section">
            <h2>🧪 Test Request Submission</h2>
            <?php if (isset($_POST['test_submit'])): ?>
                <?php
                // First check if the table has the correct structure before testing
                $can_test = true;
                $structure_issues = [];
                
                $result = $working_conn->query("DESCRIBE requests");
                if ($result) {
                    $fields = [];
                    $id_correct = false;
                    while ($row = $result->fetch_assoc()) {
                        $fields[] = $row['Field'];
                        if ($row['Field'] == 'id' && $row['Type'] == 'int' && $row['Extra'] == 'auto_increment') {
                            $id_correct = true;
                        }
                    }
                    
                    if (!in_array('fullname', $fields)) {
                        $structure_issues[] = "Missing 'fullname' field";
                        $can_test = false;
                    }
                    if (!$id_correct) {
                        $structure_issues[] = "ID field is not properly configured as INT AUTO_INCREMENT";
                        $can_test = false;
                    }
                }
                
                if (!$can_test) {
                    echo "<div class='test-result'>";
                    echo "<h3>❌ Cannot Test - Table Structure Issues:</h3>";
                    foreach ($structure_issues as $issue) {
                        echo "<div class='status error'>❌ " . $issue . "</div>";
                    }
                    echo "<div class='info'>Please fix the table structure first using the 'Fix Table Structure' button above.</div>";
                    echo "</div>";
                } else {
                    $test_fullname = $working_conn->real_escape_string($_POST['test_fullname']);
                    $test_email = $working_conn->real_escape_string($_POST['test_email']);
                    $test_username = $working_conn->real_escape_string($_POST['test_username']);
                    $test_password = password_hash($_POST['test_password'], PASSWORD_DEFAULT);
                    $test_reason = $working_conn->real_escape_string($_POST['test_reason']);

                    echo "<div class='test-result'>";
                    echo "<h3>Test Submission Results:</h3>";
                    
                    try {
                        // Test the exact same query as request_access.php
                        $sql = "INSERT INTO requests (fullname, email, username, password, reason) VALUES (?, ?, ?, ?, ?)";
                        $stmt = $working_conn->prepare($sql);
                        
                        if ($stmt) {
                            $stmt->bind_param("sssss", $test_fullname, $test_email, $test_username, $test_password, $test_reason);
                            
                            if ($stmt->execute()) {
                                echo "<div class='status success'>✅ Test submission successful!</div>";
                                echo "<p>Inserted ID: " . $working_conn->insert_id . "</p>";
                            } else {
                                echo "<div class='status error'>❌ Test submission failed!</div>";
                                echo "<p>Error: " . $stmt->error . "</p>";
                                echo "<p>Error Code: " . $stmt->errno . "</p>";
                            }
                            $stmt->close();
                        } else {
                            echo "<div class='status error'>❌ Failed to prepare statement!</div>";
                            echo "<p>Error: " . $working_conn->error . "</p>";
                        }
                    } catch (mysqli_sql_exception $e) {
                        echo "<div class='status error'>❌ Test submission failed!</div>";
                        echo "<p>Error: " . $e->getMessage() . "</p>";
                        echo "<p>This confirms the table structure needs to be fixed.</p>";
                    }
                    echo "</div>";
                }
                ?>
            <?php endif; ?>
            
            <div class="form-section">
                <h3>Submit Test Request</h3>
                <?php
                // Check if we can test
                $can_test = true;
                $result = $working_conn->query("DESCRIBE requests");
                if ($result) {
                    $fields = [];
                    while ($row = $result->fetch_assoc()) {
                        $fields[] = $row['Field'];
                    }
                    if (!in_array('fullname', $fields)) {
                        $can_test = false;
                    }
                }
                
                if (!$can_test) {
                    echo "<div class='status warning'>⚠️ Cannot test submissions until table structure is fixed. Please use 'Fix Table Structure' button above first.</div>";
                } else {
                ?>
                <form method="post">
                    <label>Full Name:</label>
                    <input type="text" name="test_fullname" value="Test User <?php echo rand(1000,9999); ?>" required>
                    
                    <label>Email:</label>
                    <input type="email" name="test_email" value="test<?php echo rand(1000,9999); ?>@example.com" required>
                    
                    <label>Username:</label>
                    <input type="text" name="test_username" value="testuser<?php echo rand(1000,9999); ?>" required>
                    
                    <label>Password:</label>
                    <input type="password" name="test_password" value="testpass123" required>
                    
                    <label>Reason:</label>
                    <textarea name="test_reason" rows="3" required>Test submission for debugging purposes</textarea>
                    
                    <button type="submit" name="test_submit">🧪 Test Submit</button>
                </form>
                <?php } ?>
            </div>
        </div>

        <div class="section">
            <h2>🔍 SQL Diagnostics</h2>
            <pre><?php
            echo "Current working credentials:\n";
            echo "Username: " . $working_creds['username'] . "\n";
            echo "Database: " . $dbname . "\n\n";
            
            echo "SQL being used in request_access.php:\n";
            echo "INSERT INTO requests (fullname, email, username, password, reason, created_at, approved) VALUES (?, ?, ?, ?, ?, NOW(), FALSE)\n\n";
            
            echo "Table creation SQL:\n";
            echo "CREATE TABLE requests (\n";
            echo "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
            echo "    fullname VARCHAR(255) NOT NULL,\n";
            echo "    username VARCHAR(255) UNIQUE NOT NULL,\n";
            echo "    email VARCHAR(255) UNIQUE NOT NULL,\n";
            echo "    password VARCHAR(255) NOT NULL,\n";
            echo "    student_card VARCHAR(255) UNIQUE,\n";
            echo "    reason TEXT,\n";
            echo "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
            echo "    approved BOOLEAN DEFAULT FALSE\n";
            echo ");\n";
            ?></pre>
        </div>
    </div>

    <div class="container">
        <div class="section">
            <h2>🔧 Why Did It Stop Working? - Troubleshooting</h2>
            <?php if ($working_conn): ?>
                <div class="info">
                    <h3>🕵️ Common Reasons Why Request Access Stops Working:</h3>
                    <ol>
                        <li><strong>Table Structure Changed:</strong> The table might have been recreated without the 'fullname' field</li>
                        <li><strong>Database Credentials Changed:</strong> MySQL root password or user permissions might have changed</li>
                        <li><strong>Database Missing:</strong> The 'access_requests1' database might have been deleted</li>
                        <li><strong>Form-PHP Mismatch:</strong> The HTML form sends 'fullname' but PHP script doesn't handle it</li>
                        <li><strong>MySQL Configuration:</strong> XAMPP MySQL settings might have been reset</li>
                    </ol>
                </div>
                
                <?php
                // Let's do some detective work
                echo "<h3>🔍 System Analysis:</h3>";
                
                // Check current PHP version
                echo "<div class='status info'>PHP Version: " . phpversion() . "</div>";
                
                // Check if the issue is with the table structure
                $structure_issues = [];
                $result = $working_conn->query("DESCRIBE requests");
                if ($result) {
                    $fields = [];
                    while ($row = $result->fetch_assoc()) {
                        $fields[] = $row['Field'];
                        if ($row['Field'] == 'id' && $row['Extra'] != 'auto_increment') {
                            $structure_issues[] = "ID field is not AUTO_INCREMENT";
                        }
                    }
                    
                    if (!in_array('fullname', $fields)) {
                        $structure_issues[] = "Missing 'fullname' field (required by form)";
                    }
                    
                    if (empty($structure_issues)) {
                        echo "<div class='status success'>✅ Table structure looks correct</div>";
                    } else {
                        echo "<div class='status error'>❌ Table structure issues found:</div>";
                        foreach ($structure_issues as $issue) {
                            echo "<div class='status warning'>⚠️ " . $issue . "</div>";
                        }
                    }
                } else {
                    echo "<div class='status error'>❌ Cannot analyze table structure - table might not exist</div>";
                }
                
                // Check if there are any records in the table
                $count_result = $working_conn->query("SELECT COUNT(*) as total FROM requests");
                if ($count_result) {
                    $count_row = $count_result->fetch_assoc();
                    $total_records = $count_row['total'];
                    
                    if ($total_records > 0) {
                        echo "<div class='status info'>📊 Found {$total_records} existing records in the table</div>";
                        
                        // Check if there are any successful recent submissions
                        $recent_result = $working_conn->query("SELECT COUNT(*) as recent FROM requests WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
                        if ($recent_result) {
                            $recent_row = $recent_result->fetch_assoc();
                            $recent_count = $recent_row['recent'];
                            
                            if ($recent_count > 0) {
                                echo "<div class='status success'>✅ {$recent_count} records were added in the last 7 days - system seems to be working</div>";
                            } else {
                                echo "<div class='status warning'>⚠️ No records added in the last 7 days - might indicate a recent issue</div>";
                            }
                        }
                    } else {
                        echo "<div class='status info'>📊 Table is empty - no previous submissions found</div>";
                    }
                }
                ?>
                
                <div class="info">
                    <h3>🛠️ Quick Fixes to Try:</h3>
                    <ol>
                        <li><strong>Fix Table Structure:</strong> Use the "Fix Table Structure" button below</li>
                        <li><strong>Reset Database:</strong> Run <code>setup_all_databases.php</code> to recreate everything</li>
                        <li><strong>Check Credentials:</strong> Verify MySQL root password with <code>diagnose_mysql.php</code></li>
                        <li><strong>Test Form:</strong> Use the test submission form below to isolate the issue</li>
                        <li><strong>Check Logs:</strong> Look at XAMPP error logs for MySQL connection issues</li>
                    </ol>
                </div>
            <?php endif; ?>
        </div>

        <?php
        if ($working_conn) {
            $working_conn->close();
        }
        ?>
    </div>
</body>
</html>
