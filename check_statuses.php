<?php
require 'Connection/connection.php';
Database::setUpConnection();

$query = "SELECT DISTINCT exam_status FROM results";
$result = Database::search($query);

echo "Exam Statuses in DB:\n";
while($row = $result->fetch_assoc()) {
    echo "  '" . $row['exam_status'] . "'\n";
}

$query2 = "
SELECT s.student_no, res.exam_status, COUNT(*) as c
FROM student s
LEFT JOIN module_enrollment me ON me.student_student_no = s.student_no
LEFT JOIN results res ON res.module_enrollment_module_enrollment_id = me.module_enrollment_id
WHERE s.student_no = 'PS/2022/145' OR s.student_no = 'PS/2022/147'
GROUP BY s.student_no, res.exam_status
";
$result2 = Database::search($query2);

echo "\nExam Status for test students:\n";
while($row = $result2->fetch_assoc()) {
    echo "  Student: " . $row['student_no'] . " | Status: " . ($row['exam_status'] ?? 'NULL') . " | Count: " . $row['c'] . "\n";
}
?>