<?php
require 'Connection/connection.php';
Database::setUpConnection();

echo "=== RESULTS TABLE ===\n";
$res = Database::search('SHOW COLUMNS FROM results');
if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo $row['Field'] . "\n";
    }
} else {
    echo "Results table not found\n";
}

echo "\n=== SAMPLE RESULTS DATA ===\n";
$res = Database::search('SELECT * FROM results LIMIT 5');
if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo json_encode($row) . "\n";
    }
} else {
    echo "No results data\n";
}
?>
