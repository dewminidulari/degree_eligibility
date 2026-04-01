<?php
require 'Connection/connection.php';
Database::setUpConnection();

// Check module table
echo "\n=== MODULE TABLE ===\n";
$res = Database::search("SHOW COLUMNS FROM `module`");
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . "\n";
}

// Check module_enrollment table
echo "\n=== MODULE_ENROLLMENT TABLE ===\n";
$res = Database::search("SHOW COLUMNS FROM `module_enrollment`");
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . "\n";
}

// Sample data
echo "\n=== SAMPLE MODULE DATA ===\n";
$res = Database::search("SELECT * FROM module LIMIT 5");
while ($row = $res->fetch_assoc()) {
    echo json_encode($row) . "\n";
}

echo "\n=== SAMPLE MODULE_ENROLLMENT DATA ===\n";
$res = Database::search("SELECT * FROM module_enrollment LIMIT 5");
while ($row = $res->fetch_assoc()) {
    echo json_encode($row) . "\n";
}
?>
