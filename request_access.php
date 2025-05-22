<?php
echo "<h2>Access Request Submitted</h2>";
echo "Full Name: " . htmlspecialchars($_POST["fullname"]) . "<br>";
echo "Email: " . htmlspecialchars($_POST["email"]) . "<br>";
echo "Desired Username: " . htmlspecialchars($_POST["username"]) . "<br>";
echo "Desired Password: " . htmlspecialchars($_POST["password"]) . "<br>";
echo "Reason: " . nl2br(htmlspecialchars($_POST["reason"])) . "<br>";
?>
