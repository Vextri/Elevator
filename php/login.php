<?php
session_start();

// Simple login handler for the elevator system
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Basic validation
    if (strlen($username) < 7 || strlen($password) < 7) {
        header("Location: ../html/login.html?error=length");
        exit();
    }
    
    // For demo purposes, accept any username/password that meets length requirements
    // In a real system, you'd check against a database
    if (!empty($username) && !empty($password)) {
        // Set session variables
        $_SESSION['username'] = htmlspecialchars($username);
        $_SESSION['login_method'] = 'manual_login';
        $_SESSION['user_id'] = uniqid(); // Generate a unique ID
        $_SESSION['login_time'] = time(); // Unix timestamp for accurate time tracking
        
        // Redirect to dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        // Invalid login
        header("Location: ../html/login.html?error=invalid");
        exit();
    }
} else {
    // Direct access to login.php, redirect to login page
    header("Location: ../html/login.html");
    exit();
}
?>
