<?php
// Enable error reporting (for debugging)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start the session
session_start();

// Connect to the database
$mysqli = new mysqli("localhost", "Blaise", "Gitdead32!32", "access_requests");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];  // Get the user ID from the URL

    // Check if user_id is a valid number to prevent SQL injection
    if (filter_var($user_id, FILTER_VALIDATE_INT)) {
        // Prepare the query to approve the user (set approved = 1)
        $query = "UPDATE requests SET approved = 1 WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $user_id);  // Bind the user_id as an integer
        if ($stmt->execute()) {
            // Set success message in session
            $_SESSION['flash_message'] = "User approved successfully!";
            
            // Redirect after a brief message to avoid re-submitting on refresh
            header("Location: user_requests.php");
            exit;  // Make sure the script execution stops after redirect
        } else {
            echo "Error approving user.";
        }
        $stmt->close();
    } else {
        echo "Invalid user ID.";
    }
} else {
    echo "No user ID provided.";
}

$mysqli->close();
?>

