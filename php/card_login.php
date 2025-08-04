<?php
// card_login.php - Simple session-based card login
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $mysqli = new mysqli("localhost", "Blaise", "Gitdead32!32", "access_requests1"); //This is a password I made up for the sake of the project it is not confidential

    if ($mysqli->connect_error) {
        throw new Exception('Database connection failed: ' . $mysqli->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $student_card = isset($_POST['student_card']) ? trim($_POST['student_card']) : '';
        $action = isset($_POST['action']) ? $_POST['action'] : '';
        
        if (empty($student_card) || $action !== 'card_login') {
            throw new Exception('Invalid request data');
        }
        
        // Query the database
        $sql = "SELECT id, student_card, email, username, approved FROM requests WHERE student_card = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $student_card);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if ($user['approved'] == 1) {
                // Create session immediately
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['student_card'] = $user['student_card'];
                $_SESSION['login_method'] = 'card_scan';
                $_SESSION['login_time'] = time(); // Unix timestamp for accurate time tracking
                
                // Log successful card login
                $client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Card Reader System';
                $log_reason = "Card scan login successful - IP: {$client_ip} - System: {$user_agent}";
                
                $log_sql = "INSERT INTO access_logs (student_card, access_time, success, reason) VALUES (?, NOW(), 1, ?)";
                $log_stmt = $mysqli->prepare($log_sql);
                if ($log_stmt) {
                    $log_stmt->bind_param("ss", $user['student_card'], $log_reason);
                    $log_stmt->execute();
                    $log_stmt->close();
                }
                
                // Also create a status file for browser detection
                $status_data = [
                    'status' => 'logged_in',
                    'user_id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'student_card' => $user['student_card'],
                    'login_method' => 'card_scan',
                    'redirect_url' => 'dashboard.php',
                    'timestamp' => time(),
                    'message' => 'Card login successful'
                ];
                file_put_contents('card_login_status.txt', json_encode($status_data));
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'user_id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email']
                ]);
            } else {
                // Log failed card login - account not approved
                $client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Card Reader System';
                $log_reason = "Card scan login FAILED (account not approved) - Card: {$student_card} - IP: {$client_ip} - System: {$user_agent}";
                
                $log_sql = "INSERT INTO access_logs (student_card, access_time, success, reason) VALUES (?, NOW(), 0, ?)";
                $log_stmt = $mysqli->prepare($log_sql);
                if ($log_stmt) {
                    $log_stmt->bind_param("ss", $student_card, $log_reason);
                    $log_stmt->execute();
                    $log_stmt->close();
                }
                
                echo json_encode([
                    'success' => false,
                    'message' => 'Account not approved'
                ]);
            }
        } else {
            // Log failed card login - card not found
            $client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Card Reader System';
            $log_reason = "Card scan login FAILED (card not found) - Card: {$student_card} - IP: {$client_ip} - System: {$user_agent}";
            
            $log_sql = "INSERT INTO access_logs (student_card, access_time, success, reason) VALUES (?, NOW(), 0, ?)";
            $log_stmt = $mysqli->prepare($log_sql);
            if ($log_stmt) {
                $log_stmt->bind_param("ss", $student_card, $log_reason);
                $log_stmt->execute();
                $log_stmt->close();
            }
            
            echo json_encode([
                'success' => false,
                'message' => 'Card not found'
            ]);
        }
        $stmt->close();
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$mysqli->close();
?>
