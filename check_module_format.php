<?php
require 'Connection/connection.php';
Database::setUpConnection();

$query = "SELECT DISTINCT module_code FROM module LIMIT 20";
$result = Database::search($query);

echo "Modules from 'module' table:\n";
while($row = $result->fetch_assoc()) {
    echo "  '" . $row['module_code'] . "'\n";
}

echo "\nTesting SQL LIKE clause from get_students.php:\n";
$query = "
SELECT module_code, 
       CASE WHEN module_code NOT LIKE 'ACLT%' THEN 'Matches NOT LIKE ACLT%' ELSE 'Filtered out' END as status1,
       CASE WHEN module_code NOT LIKE 'CMSK%' THEN 'Matches NOT LIKE CMSK%' ELSE 'Filtered out' END as status2,
       CASE WHEN module_code NOT LIKE 'MGMT%' THEN 'Matches NOT LIKE MGMT%' ELSE 'Filtered out' END as status3
FROM module 
WHERE module_code LIKE 'ACLT%' OR module_code LIKE 'CMSK%' OR module_code LIKE 'MGMT%'
LIMIT 10;
";

$result = Database::search($query);
while($row = $result->fetch_assoc()) {
    echo $row['module_code'] . " -> " . $row['status1'] . " | " . $row['status2'] . " | " . $row['status3'] . "\n";
}

echo "\nDone!\n";
