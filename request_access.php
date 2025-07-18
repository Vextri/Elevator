<?php
// Enhanced request_access.php with better error handling and diagnostics
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database credentials - try multiple configurations for portability
$credentials = [
    ['username' => 'Blaise', 'password' => 'Gitdead32!32'],
    ['username' => 'root', 'password' => ''],
    ['username' => 'root', 'password' => 'ese'],
    ['username' => 'ese', 'password' => 'ese']
];

$servername = "localhost";
$dbname = "access_requests1";

function testConnection($servername, $username, $password, $dbname) {
    $conn = new mysqli($servername, $username, $password, $dbname);
    return $conn->connect_error ? false : $conn;
}

// Find working connection
$conn = null;
$working_creds = null;

foreach ($credentials as $cred) {
    $test_conn = testConnection($servername, $cred['username'], $cred['password'], $dbname);
    if ($test_conn) {
        $conn = $test_conn;
        $working_creds = $cred;
        break;
    }
}

if (!$conn) {
    die("Connection failed: Unable to connect with any of the configured credentials. Please check your MySQL setup.");
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate input data
    if (empty($_POST['email']) || empty($_POST['username']) || empty($_POST['password']) || empty($_POST['reason']) || empty($_POST['fullname'])) {
        echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px; border-radius: 5px;'>";
        echo "Error: All fields are required!";
        echo "</div>";
    } else {
        // Get form data and validate
        $fullname = trim($_POST['fullname']);
        $email = trim($_POST['email']);
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $reason = trim($_POST['reason']);
        
        // Additional validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px; border-radius: 5px;'>";
            echo "Error: Invalid email format!";
            echo "</div>";
        } elseif (strlen($username) < 3) {
            echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px; border-radius: 5px;'>";
            echo "Error: Username must be at least 3 characters long!";
            echo "</div>";
        } elseif (strlen($password) < 6) {
            echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px; border-radius: 5px;'>";
            echo "Error: Password must be at least 6 characters long!";
            echo "</div>";
        } elseif (strlen($fullname) < 2) {
            echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px; border-radius: 5px;'>";
            echo "Error: Full name must be at least 2 characters long!";
            echo "</div>";
        } else {
            // Escape strings for SQL
            $fullname = $conn->real_escape_string($fullname);
            $email = $conn->real_escape_string($email);
            $username = $conn->real_escape_string($username);
            $reason = $conn->real_escape_string($reason);
            
            // Hash the password for security
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Check for existing username or email first
            $check_sql = "SELECT id FROM requests WHERE username = ? OR email = ?";
            $check_stmt = $conn->prepare($check_sql);
            
            if ($check_stmt) {
                $check_stmt->bind_param("ss", $username, $email);
                $check_stmt->execute();
                $result = $check_stmt->get_result();
                
                if ($result->num_rows > 0) {
                    echo "<div style='color: orange; padding: 10px; border: 1px solid orange; margin: 10px; border-radius: 5px;'>";
                    echo "Error: Username or email already exists in the system!";
                    echo "</div>";
                } else {
                    // Proceed with insertion - explicitly exclude id to ensure AUTO_INCREMENT works
                    $sql = "INSERT INTO requests (fullname, email, username, password, reason, created_at, approved) VALUES (?, ?, ?, ?, ?, NOW(), FALSE)";
                    $stmt = $conn->prepare($sql);
                    
                    if ($stmt) {
                        $stmt->bind_param("sssss", $fullname, $email, $username, $hashedPassword, $reason);
                        
                        if ($stmt->execute()) {
                            $new_id = $conn->insert_id;
                            echo "<div style='color: green; padding: 10px; border: 1px solid green; margin: 10px; border-radius: 5px;'>";
                            echo "✅ Request submitted successfully! Request ID: " . $new_id;
                            echo "<br>Your request will be reviewed by an administrator.";
                            echo "<br><br><a href='request_access.html' style='color: blue;'>Submit another request</a>";
                            echo "</div>";
                        } else {
                            echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px; border-radius: 5px;'>";
                            echo "Error executing statement: " . $stmt->error;
                            echo "<br>Error code: " . $stmt->errno;
                            echo "<br>SQL State: " . $conn->sqlstate;
                            echo "</div>";
                        }
                        $stmt->close();
                    } else {
                        echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px; border-radius: 5px;'>";
                        echo "Error preparing statement: " . $conn->error;
                        echo "</div>";
                    }
                }
                $check_stmt->close();
            } else {
                echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px; border-radius: 5px;'>";
                echo "Error preparing check statement: " . $conn->error;
                echo "</div>";
            }
        }
    }
    
    // Close the connection
    $conn->close();
}
?>