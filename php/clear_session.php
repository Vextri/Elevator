<?php
// clear_session.php - Clear all session data
session_start();

// Allow both GET and POST methods
$method = $_SERVER['REQUEST_METHOD'];

// Destroy the session
session_destroy();

// Also unset all session variables
$_SESSION = array();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Set JSON content type
header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'message' => 'Session cleared successfully',
    'method' => $method
]);
?>
