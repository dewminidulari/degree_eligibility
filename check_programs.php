<?php
require_once 'connection/connection.php';
try {
    Database::setUpConnection();
    $res = Database::search("SELECT * FROM program");
    while ($row = $res->fetch_assoc()) {
        echo "ID: " . $row['program_id'] . " | Name: '" . $row['program_name'] . "'\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
