<?php
// Entry point for restructured elevator system
// Redirect to the proper login page

// Check if accessing setup first
if (isset($_GET['setup'])) {
    header("Location: php/setup_center.php");
    exit;
}

// Default redirect to login
header("Location: html/website.html");
exit;
?>