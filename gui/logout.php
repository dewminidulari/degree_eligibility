<?php
session_start();

// CORRECT PATH: Go up one level from gui to degree_eligibility, then into Connection
require_once '../Connection/connection.php';

// Clear all session variables
$_SESSION = array();

// If you're using session cookies, destroy the cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// CORRECT PATH: Just the filename since both files are in the same folder
header("Location: faculty-login.php");
exit();
?>