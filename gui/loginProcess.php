<?php
session_start();
require "../connection/connection.php";

$username = $_POST["username"] ?? "";
$password = $_POST["password"] ?? "";
$remember = $_POST["remember"] ?? false;

if (empty($username) || empty($password)) {
    echo "All fields required";
    exit();
}

$rs = Database::search("SELECT * FROM admin_signin WHERE username='$username'");

if ($rs->num_rows == 1) {

    $user = $rs->fetch_assoc();

    if ($password === $user["password"]){

        $_SESSION["admin"] = $user["username"];

        // Log the admin login
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $log_entry = "[" . date("Y-m-d H:i:s") . "] Admin logged in: " . $user["username"] . " (IP: $ip_address)\n";
        file_put_contents(__DIR__ . "/../admin_logins.log", $log_entry, FILE_APPEND);

        if ($remember === "1") {
            setcookie("admin_username", $username, time() + (60*60*24*30), "/");
            setcookie("admin_password", $password, time() + (60*60*24*30), "/");
        } else {
            setcookie("admin_username", "", time() - 3600, "/");
            setcookie("admin_password", "", time() - 3600, "/");
        }

        echo "success";
    } else {
        echo "Incorrect password";
    }

} else {
    echo "Account not found";
}