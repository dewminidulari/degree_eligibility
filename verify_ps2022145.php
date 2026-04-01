<?php
require 'Connection/connection.php';
Database::setUpConnection();

echo "Running query with NO ACLT/CMSK/MGMT exclusions for PS/2022/145:\n";
$query1 = "
SELECT 
    SUM(CASE WHEN res.exam_status = 'Completed' AND m.is_gpa_module = 1 AND gr.grade_code != 'AB' THEN gr.grade_point * m.credit_value ELSE 0 END) as total_points,
    SUM(CASE WHEN res.exam_status = 'Completed' AND m.is_gpa_module = 1 AND gr.grade_code != 'AB' THEN m.credit_value ELSE 0 END) as total_credits
FROM student s
LEFT JOIN module_enrollment me ON me.student_student_no = s.student_no
LEFT JOIN results res ON res.module_enrollment_module_enrollment_id = me.module_enrollment_id
LEFT JOIN grade gr ON gr.grade_id = res.grade_grade_id
LEFT JOIN module m ON res.module_module_code = m.module_code
WHERE s.student_no = 'PS/2022/145';
";
$r1 = Database::search($query1)->fetch_assoc();
echo "  Total Points: {$r1['total_points']}, Total Credits: {$r1['total_credits']}\n";

echo "Running query WITH ACLT/CMSK/MGMT exclusions for PS/2022/145:\n";
$query2 = "
SELECT 
    SUM(CASE WHEN m.is_gpa_module = 1 AND gr.grade_code IS NOT NULL AND gr.grade_code != 'AB' AND m.module_code NOT LIKE 'ACLT%' AND m.module_code NOT LIKE 'CMSK%' AND m.module_code NOT LIKE 'MGMT%' THEN gr.grade_point * m.credit_value ELSE 0 END) as total_points,
    SUM(CASE WHEN m.is_gpa_module = 1 AND gr.grade_code IS NOT NULL AND gr.grade_code != 'AB' AND m.module_code NOT LIKE 'ACLT%' AND m.module_code NOT LIKE 'CMSK%' AND m.module_code NOT LIKE 'MGMT%' THEN m.credit_value ELSE 0 END) as total_credits
FROM student s
LEFT JOIN module_enrollment me ON me.student_student_no = s.student_no
LEFT JOIN results res ON res.module_enrollment_module_enrollment_id = me.module_enrollment_id
LEFT JOIN grade gr ON gr.grade_id = res.grade_grade_id
LEFT JOIN module m ON res.module_module_code = m.module_code
WHERE s.student_no = 'PS/2022/145';
";
$r2 = Database::search($query2)->fetch_assoc();
echo "  Total Points: {$r2['total_points']}, Total Credits: {$r2['total_credits']}\n";

?>