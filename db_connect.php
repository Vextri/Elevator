<?php
// Database credentials
$servername = "localhost";   // MySQL server (usually localhost)
$username = "root";          // Default MySQL username in XAMPP is root
$password = "";              // Default password is empty in XAMPP
$dbname = "my_website_db";   // The name of the database you created

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully to the database!";
?>
