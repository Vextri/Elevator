<?php
// card_login.php - Simple session-based card login
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $mysqli = new mysqli("localhost", "Blaise", "Gitdead32!32", "access_requests1");

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
                $_SESSION['login_time'] = date('Y-m-d H:i:s');
                
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
                echo json_encode([
                    'success' => false,
                    'message' => 'Account not approved'
                ]);
            }
        } else {
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
