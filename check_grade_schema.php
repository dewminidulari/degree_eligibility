<?php
require 'Connection/connection.php';
Database::setUpConnection();

echo "=== GRADE TABLE ===\n";
$res = Database::search('SHOW COLUMNS FROM grade');
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . "\n";
}

echo "\n=== ENROLLMENT TABLE ===\n";
$res = Database::search('SHOW COLUMNS FROM enrollment');
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . "\n";
}

echo "\n=== SAMPLE GRADE DATA ===\n";
$res = Database::search('SELECT * FROM grade LIMIT 3');
while ($row = $res->fetch_assoc()) {
    echo json_encode($row) . "\n";
}

echo "\n=== SAMPLE ENROLLMENT DATA ===\n";
$res = Database::search('SELECT * FROM enrollment LIMIT 3');
while ($row = $res->fetch_assoc()) {
    echo json_encode($row) . "\n";
}
?>
