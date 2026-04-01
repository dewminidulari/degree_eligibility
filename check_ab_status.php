<?php
require 'Connection/connection.php';
Database::setUpConnection();

echo "=== CHECKING AB GRADES ===\n";
$res = Database::search('SELECT DISTINCT exam_status, grade_code FROM results r LEFT JOIN grade g ON r.grade_grade_id = g.grade_id WHERE g.grade_code = "AB" LIMIT 10');
while ($row = $res->fetch_assoc()) {
    echo 'Status: ' . $row['exam_status'] . ' | Grade: ' . $row['grade_code'] . "\n";
}

echo "\n=== CHECKING ALL EXAM STATUSES ===\n";
$res = Database::search('SELECT DISTINCT exam_status FROM results');
while ($row = $res->fetch_assoc()) {
    echo 'Status: ' . $row['exam_status'] . "\n";
}

echo "\n=== CHECKING RESULTS WITH AB GRADE ===\n";
$res = Database::search('SELECT r.exam_status, g.grade_code, g.grade_point FROM results r LEFT JOIN grade g ON r.grade_grade_id = g.grade_id WHERE g.grade_code = "AB" LIMIT 5');
while ($row = $res->fetch_assoc()) {
    echo 'Status: ' . $row['exam_status'] . ' | Grade: ' . $row['grade_code'] . ' | Points: ' . $row['grade_point'] . "\n";
}
?>