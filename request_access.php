<?php
// Database credentials
$servername = "localhost";   // MySQL server (usually localhost)
$username = "Blaise";          // Default MySQL username in XAMPP is root
$password = "Gitdead32!32";              // Default password is empty in XAMPP
$dbname = "access_requests"; // The name of the database you created

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $email = $conn->real_escape_string($_POST['email']);
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);
    $reason = $conn->real_escape_string($_POST['reason']);

    // You should hash the password before storing it in the database for security
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // SQL query to insert data into the table
    $sql = "INSERT INTO requests (fullname, email, username, password, reason)
            VALUES ('$fullname', '$email', '$username', '$hashedPassword', '$reason')";

    if ($conn->query($sql) === TRUE) {
        echo "Request submitted successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    // Close the connection
    $conn->close();
}
?>