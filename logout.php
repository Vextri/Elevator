<?php
// logout.php - Simple logout script
session_start();

// Destroy all session data
session_destroy();

// Redirect to login page
header('Location: login.html');
exit();
?>
