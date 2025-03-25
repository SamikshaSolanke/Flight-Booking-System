<?php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Delete cookies
if (isset($_COOKIE['user_login'])) {
    setcookie("user_login", "", time() - 3600, "/");
}
if (isset($_COOKIE['user_id'])) {
    setcookie("user_id", "", time() - 3600, "/");
}

// Redirect to login page
header("Location: login.php");
exit();
?>