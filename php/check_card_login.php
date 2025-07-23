<?php
// check_card_login.php - Endpoint for browser to check if a card login has occurred
session_start();

// Set JSON content type
header('Content-Type: application/json');

// Enable CORS if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // First check for file-based card login status
    $status_file = 'card_login_status.txt';
    
    if (file_exists($status_file)) {
        $content = file_get_contents($status_file);
        $data = json_decode($content, true);
        
        // Check if status is recent (within last 30 seconds)
        if ($data && isset($data['timestamp']) && (time() - $data['timestamp']) < 30) {
            // Create browser session from file data
            $_SESSION['user_id'] = $data['user_id'];
            $_SESSION['username'] = $data['username'];
            $_SESSION['email'] = $data['email'];
            $_SESSION['student_card'] = $data['student_card'];
            $_SESSION['login_method'] = 'card_scan';
            $_SESSION['login_time'] = date('Y-m-d H:i:s');
            
            // Delete the status file so it's only used once
            unlink($status_file);
            
            echo json_encode([
                'status' => 'logged_in',
                'message' => 'Card login successful',
                'user_id' => $data['user_id'],
                'username' => $data['username'],
                'redirect_url' => 'dashboard.php'
            ]);
            exit;
        } else {
            // File is too old, delete it
            unlink($status_file);
        }
    }
    
    // Check if user is already logged in via session (manual or previous card login)
    if (isset($_SESSION['user_id']) && isset($_SESSION['login_method']) && $_SESSION['login_method'] === 'card_scan') {
        // User is logged in via card - return success
        echo json_encode([
            'status' => 'logged_in',
            'message' => 'Card login successful',
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'] ?? 'Unknown',
            'redirect_url' => 'dashboard.php'
        ]);
    } else if (isset($_SESSION['user_id'])) {
        // User is logged in but not via card
        echo json_encode([
            'status' => 'logged_in_manual',
            'message' => 'Manual login detected',
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'] ?? 'Unknown',
            'redirect_url' => 'dashboard.php'
        ]);
    } else {
        // No login detected
        echo json_encode([
            'status' => 'not_logged_in',
            'message' => 'No active session'
        ]);
    }
} catch (Exception $e) {
    // Error occurred
    echo json_encode([
        'status' => 'error',
        'message' => 'Error checking login status: ' . $e->getMessage()
    ]);
}
?>
