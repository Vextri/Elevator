<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session to check for existing card login
session_start();

// Check if user is already logged in via card scan
if (isset($_SESSION['user_id']) && isset($_SESSION['login_method']) && $_SESSION['login_method'] === 'card_scan') {
    // User already logged in via card scan
    header("Location: dashboard.php");
    exit();
}

// Connect to the database
$mysqli = new mysqli("localhost", "Blaise", "Gitdead32!32", "access_requests1");

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form input
    $username = $_POST['username'];
    $password = $_POST['password'];

    // DEBUG: Show what we're searching for
    echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px; border: 1px solid #ccc;'>";
    echo "<h3>DEBUG INFO:</h3>";
    echo "Searching for username: '" . htmlspecialchars($username) . "'<br>";
    echo "Username length: " . strlen($username) . "<br>";
    echo "Password length: " . strlen($password) . "<br>";
    echo "</div>";

    // Query the database for the username
    $sql = "SELECT id, username, password, email, student_card, approved FROM requests WHERE username = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // DEBUG: Show query results
    echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px; border: 1px solid #ccc;'>";
    echo "Rows found: " . $result->num_rows . "<br>";
    echo "</div>";

    if ($result->num_rows > 0) {
        // User exists, fetch the user data
        $user = $result->fetch_assoc();
        
        // DEBUG: Show user data
        echo "<div style='background: #e8f5e8; padding: 10px; margin: 10px; border: 1px solid #4caf50;'>";
        echo "<h3>USER FOUND:</h3>";
        echo "Username from DB: '" . htmlspecialchars($user['username']) . "'<br>";
        echo "Approved status: " . $user['approved'] . "<br>";
        echo "Password hash (first 30 chars): " . substr($user['password'], 0, 30) . "...<br>";
        echo "Entered password: '" . htmlspecialchars($password) . "'<br>";
        echo "</div>";

        // Check if account is approved
        if ($user['approved'] != 1) {
            echo "<div style='color: red; text-align: center; margin: 20px;'>";
            echo "Account not approved - pending administrator approval";
            echo "</div>";
            echo "<a href='../html/login.html' style='display: block; text-align: center;'>Back to Login</a>";
            $stmt->close();
            $mysqli->close();
            exit();
        }

        // Verify the entered password against the hashed password in the database
        echo "<div style='background: #fff3cd; padding: 10px; margin: 10px; border: 1px solid #ffc107;'>";
        echo "<h3>PASSWORD VERIFICATION:</h3>";
        echo "Attempting to verify password...<br>";
        $verification_result = password_verify($password, $user['password']);
        echo "Password verification result: " . ($verification_result ? 'TRUE (SUCCESS)' : 'FALSE (FAILED)') . "<br>";
        echo "</div>";
        
        if ($verification_result) {
            // Password matches, allow login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['student_card'] = $user['student_card'];
            $_SESSION['login_method'] = 'manual_login';
            $_SESSION['login_time'] = date('Y-m-d H:i:s');
            
            // Log successful login attempt
            $client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            $log_reason = "Manual web login successful - IP: {$client_ip} - Browser: " . substr($user_agent, 0, 100);
            
            $log_sql = "INSERT INTO access_logs (student_card, access_time, success, reason) VALUES (?, NOW(), 1, ?)";
            $log_stmt = $mysqli->prepare($log_sql);
            if ($log_stmt) {
                $log_stmt->bind_param("ss", $user['student_card'], $log_reason);
                $log_stmt->execute();
                $log_stmt->close();
            }
            
            header("Location: dashboard.php");
            exit;
        } else {
            // Log failed login attempt
            $client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            $log_reason = "Manual web login FAILED (wrong password) - Username: {$username} - IP: {$client_ip} - Browser: " . substr($user_agent, 0, 100);
            
            $log_sql = "INSERT INTO access_logs (student_card, access_time, success, reason) VALUES (?, NOW(), 0, ?)";
            $log_stmt = $mysqli->prepare($log_sql);
            if ($log_stmt) {
                $student_card = $user['student_card'] ?? 'unknown';
                $log_stmt->bind_param("ss", $student_card, $log_reason);
                $log_stmt->execute();
                $log_stmt->close();
            }
            
            echo "<div style='color: red; text-align: center; margin: 20px;'>";
            echo "Incorrect password!";
            echo "</div>";
            echo "<a href='../html/login.html' style='display: block; text-align: center;'>Back to Login</a>";
        }
    } else {
        // Log failed login attempt - user not found
        $client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $log_reason = "Manual web login FAILED (user not found) - Username: {$username} - IP: {$client_ip} - Browser: " . substr($user_agent, 0, 100);
        
        $log_sql = "INSERT INTO access_logs (student_card, access_time, success, reason) VALUES (?, NOW(), 0, ?)";
        $log_stmt = $mysqli->prepare($log_sql);
        if ($log_stmt) {
            $unknown_card = 'unknown';
            $log_stmt->bind_param("ss", $unknown_card, $log_reason);
            $log_stmt->execute();
            $log_stmt->close();
        }
        
        echo "<div style='color: red; text-align: center; margin: 20px;'>";
        echo "User not found!";
        echo "</div>";
        echo "<a href='../html/login.html' style='display: block; text-align: center;'>Back to Login</a>";
    }

    $stmt->close();
}

$mysqli->close();
?>