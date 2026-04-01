<?php
require 'Connection/connection.php';
Database::setUpConnection();

echo "=== SAMPLE RESULTS DATA ===\n";
$res = Database::search('SELECT * FROM results LIMIT 10');
while ($row = $res->fetch_assoc()) {
    echo json_encode($row) . "\n";
}

echo "\n=== GRADE TABLE ===\n";
$res = Database::search('SELECT * FROM grade');
while ($row = $res->fetch_assoc()) {
    echo $row['grade_code'] . ' = ' . $row['grade_point'] . "\n";
}

echo "\n=== CHECKING A STUDENT WITH GPA 4 ===\n";
$query = "
SELECT s.student_no, s.name_with_initial,
       res.exam_status, gr.grade_code, gr.grade_point, m.credit_value
FROM student s
LEFT JOIN module_enrollment me ON me.student_student_no = s.student_no
LEFT JOIN results res ON res.module_enrollment_module_enrollment_id = me.module_enrollment_id
LEFT JOIN grade gr ON gr.grade_id = res.grade_grade_id
LEFT JOIN module m ON m.module_code = res.module_module_code
WHERE s.student_no = 'PS/2022/047'
ORDER BY res.exam_status, m.module_code
";

$result = Database::search($query);
while ($row = $result->fetch_assoc()) {
    echo "Student: {$row['student_no']} - {$row['name_with_initial']} | Status: {$row['exam_status']} | Grade: {$row['grade_code']} | Points: {$row['grade_point']} | Credits: {$row['credit_value']}\n";
}
?>