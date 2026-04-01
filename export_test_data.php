<?php
require 'Connection/connection.php';
Database::setUpConnection();

$query1 = "SELECT * FROM student WHERE student_no IN ('PS/2022/145', 'PS/2022/147', 'PS/2022/047')";
$res1 = Database::search($query1);
$students = [];
while($row = $res1->fetch_assoc()) $students[] = $row;

$query2 = "
SELECT s.student_no, m.module_code, m.credit_value, gr.grade_code, res.exam_status
FROM student s
LEFT JOIN module_enrollment me ON s.student_no = me.student_student_no
LEFT JOIN results res ON me.module_enrollment_id = res.module_enrollment_module_enrollment_id
LEFT JOIN grade gr ON res.grade_grade_id = gr.grade_id
LEFT JOIN module m ON res.module_module_code = m.module_code
WHERE s.student_no IN ('PS/2022/145', 'PS/2022/147', 'PS/2022/047')";

$res2 = Database::search($query2);
$courses = [];
while($row = $res2->fetch_assoc()) {
    if ($row['module_code']) $courses[] = $row;
}

$data = [
    'students' => $students,
    'courses' => $courses
];
file_put_contents('test_data.json', json_encode($data));
echo "Exported test data.\n";
?>
