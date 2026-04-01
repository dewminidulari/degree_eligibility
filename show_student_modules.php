<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'Connection/connection.php';
Database::setUpConnection();

$query = "
SELECT s.student_no, m.module_code, m.module_name, m.credit_value, gr.grade_code, gr.grade_point, m.is_gpa_module
FROM student s
LEFT JOIN module_enrollment me ON s.student_no = me.student_student_no
LEFT JOIN results res ON me.module_enrollment_id = res.module_enrollment_module_enrollment_id
LEFT JOIN grade gr ON res.grade_grade_id = gr.grade_id
LEFT JOIN module m ON res.module_module_code = m.module_code
WHERE s.student_no = 'PS/2022/145' 
   OR s.student_no = 'PS/2022/147'
   OR s.student_no = 'PS/2022/157'
ORDER BY s.student_no, m.module_code;
";

$result = Database::search($query);

echo str_pad('Student', 15) . str_pad('Module', 15) . str_pad('GPA Module', 12) . str_pad('Grade', 8) . str_pad('Credits', 8) . str_pad('Points', 8) . "\n";
echo str_repeat("-", 66) . "\n";

while($row = $result->fetch_assoc()) {
    echo str_pad($row['student_no'], 15);
    echo str_pad($row['module_code'], 15);
    echo str_pad($row['is_gpa_module'] ? 'Yes' : 'No', 12);
    echo str_pad($row['grade_code'] ?? 'N/A', 8);
    echo str_pad($row['credit_value'] ?? '0', 8);
    echo str_pad($row['grade_point'] ?? '0', 8);
    echo "\n";
}
?>
