<?php
require 'Connection/connection.php';
Database::setUpConnection();

echo "All students in database:\n";
$result = Database::search("SELECT student_no, full_name, program_program_id FROM student ORDER BY student_no LIMIT 10");
while ($row = $result->fetch_assoc()) {
    echo $row['student_no'] . " | " . $row['full_name'] . "\n";
}
?>
