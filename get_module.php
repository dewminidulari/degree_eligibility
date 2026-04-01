<?php
require_once 'connection/connection.php';
try {
    Database::setUpConnection();
    $res = Database::search("SELECT module_code FROM module LIMIT 1");
    if ($row = $res->fetch_assoc()) {
        echo "Valid Module: " . $row['module_code'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
