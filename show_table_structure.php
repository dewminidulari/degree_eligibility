<?php
require 'Connection/connection.php';
Database::setUpConnection();

echo "=== Module Enrollment Table Structure ===\n";
$result = Database::search('SHOW CREATE TABLE `module_enrollment`');
if ($row = $result->fetch_assoc()) {
    echo $row['Create Table'];
}

echo "\n\n=== Current Enrollments for PS/2022/078 ===\n";
$result = Database::search("SELECT * FROM module_enrollment WHERE student_student_no = 'PS/2022/078'");
while ($row = $result->fetch_assoc()) {
    echo json_encode($row) . "\n";
}
?>
