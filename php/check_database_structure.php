<?php
// check_database_structure.php - Quick check of your database tables
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Structure Check</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; background: #d4edda; padding: 10px; margin: 10px 0; }
        .error { color: red; background: #f8d7da; padding: 10px; margin: 10px 0; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Database Structure Check</h1>
    
    <?php
    try {
        // Check access_requests1 database
        echo "<h2>access_requests1 Database:</h2>";
        $db = new mysqli("localhost", "Blaise", "Gitdead32!32", "access_requests1"); //This is a password I made up for the sake of the project it is not confidential
        
        if ($db->connect_error) {
            echo "<div class='error'>Connection failed: " . $db->connect_error . "</div>";
        } else {
            echo "<div class='success'>Connected successfully to access_requests1</div>";
            
            // Show all tables
            $result = $db->query("SHOW TABLES");
            echo "<h3>Tables in access_requests1:</h3>";
            echo "<ul>";
            while ($row = $result->fetch_array()) {
                echo "<li>" . $row[0] . "</li>";
            }
            echo "</ul>";
            
            // Try to show structure of each table
            $result = $db->query("SHOW TABLES");
            while ($row = $result->fetch_array()) {
                $table_name = $row[0];
                echo "<h4>Structure of table: $table_name</h4>";
                $structure = $db->query("DESCRIBE $table_name");
                echo "<table>";
                echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
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
                
                // Show sample data if it looks like a users table
                if (strpos(strtolower($table_name), 'user') !== false || strpos(strtolower($table_name), 'request') !== false) {
                    echo "<h5>Sample data from $table_name:</h5>";
                    $sample = $db->query("SELECT * FROM $table_name LIMIT 3");
                    if ($sample && $sample->num_rows > 0) {
                        echo "<table>";
                        $fields = $sample->fetch_fields();
                        echo "<tr>";
                        foreach ($fields as $field) {
                            echo "<th>" . $field->name . "</th>";
                        }
                        echo "</tr>";
                        
                        $sample->data_seek(0);
                        while ($row = $sample->fetch_assoc()) {
                            echo "<tr>";
                            foreach ($row as $value) {
                                echo "<td>" . htmlspecialchars($value) . "</td>";
                            }
                            echo "</tr>";
                        }
                        echo "</table>";
                    }
                }
            }
        }
        
        echo "<h2>elevator_lockout_db Database:</h2>";
        $lockout_db = new mysqli("localhost", "root", "", "elevator_lockout_db");
        
        if ($lockout_db->connect_error) {
            echo "<div class='error'>Connection failed: " . $lockout_db->connect_error . "</div>";
        } else {
            echo "<div class='success'>Connected successfully to elevator_lockout_db</div>";
            
            $result = $lockout_db->query("SHOW TABLES");
            echo "<h3>Tables in elevator_lockout_db:</h3>";
            echo "<ul>";
            while ($row = $result->fetch_array()) {
                echo "<li>" . $row[0] . "</li>";
            }
            echo "</ul>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>Error: " . $e->getMessage() . "</div>";
    }
    ?>
</body>
</html>
