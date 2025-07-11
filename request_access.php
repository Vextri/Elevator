<?php
// Database credentials
$servername = "localhost";   // MySQL server (usually localhost)
$username = "Blaise";          // Default MySQL username in XAMPP is root
$password = "Gitdead32!32";              // Default password is empty in XAMPP
$dbname = "access_requests1"; // The name of the database you created

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = $conn->real_escape_string($_POST['email']);
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);
    $reason = $conn->real_escape_string($_POST['reason']);

    // You should hash the password before storing it in the database for security
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Use prepared statement for better security and error handling
    $sql = "INSERT INTO requests (email, username, password, reason) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("ssss", $email, $username, $hashedPassword, $reason);
        
        if ($stmt->execute()) {
            echo "Request submitted successfully!";
        } else {
            echo "Error executing statement: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }

    // Close the connection
    $conn->close();
}
?>