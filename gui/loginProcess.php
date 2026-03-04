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

        if ($remember) {
            setcookie("admin_username", $username, time() + (60*60*24*30), "/");
        }

        echo "success";
    } else {
        echo "Incorrect password";
    }

} else {
    echo "Account not found";
}