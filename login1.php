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

    // Query the database for the username
    $sql = "SELECT id, username, password, email, student_card, approved FROM requests WHERE username = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // User exists, fetch the user data
        $user = $result->fetch_assoc();

        // Check if account is approved
        if ($user['approved'] != 1) {
            echo "<div style='color: red; text-align: center; margin: 20px;'>";
            echo "Account not approved - pending administrator approval";
            echo "</div>";
            echo "<a href='login.html' style='display: block; text-align: center;'>Back to Login</a>";
            $stmt->close();
            $mysqli->close();
            exit();
        }

        // Verify the entered password against the hashed password in the database
        if (password_verify($password, $user['password'])) {
            // Password matches, allow login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['student_card'] = $user['student_card'];
            $_SESSION['login_method'] = 'manual_login';
            $_SESSION['login_time'] = date('Y-m-d H:i:s');
            
            // Log the manual login attempt
            $log_sql = "INSERT INTO access_logs (student_card, access_time, success, reason, user_id) VALUES (?, NOW(), 1, 'Manual web login', ?)";
            $log_stmt = $mysqli->prepare($log_sql);
            if ($log_stmt) {
                $log_stmt->bind_param("ss", $user['student_card'], $user['id']);
                $log_stmt->execute();
                $log_stmt->close();
            }
            
            header("Location: dashboard.php");
            exit;
        } else {
            echo "<div style='color: red; text-align: center; margin: 20px;'>";
            echo "Incorrect password!";
            echo "</div>";
            echo "<a href='login.html' style='display: block; text-align: center;'>Back to Login</a>";
        }
    } else {
        echo "<div style='color: red; text-align: center; margin: 20px;'>";
        echo "User not found!";
        echo "</div>";
        echo "<a href='login.html' style='display: block; text-align: center;'>Back to Login</a>";
    }

    $stmt->close();
}

$mysqli->close();
?>
