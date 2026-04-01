<?php
require 'Connection/connection.php';
Database::setUpConnection();

$query1 = "
SELECT s.student_no,
       ROUND(
           COALESCE(
               SUM(CASE
                   WHEN m.is_gpa_module = 1 AND gr.grade_code != 'AB' AND m.module_code NOT LIKE 'ACLT%' AND m.module_code NOT LIKE 'CMSK%' AND m.module_code NOT LIKE 'MGMT%' THEN gr.grade_point * m.credit_value
                   ELSE 0
               END) /
               NULLIF(
                   SUM(CASE
                       WHEN m.is_gpa_module = 1 AND gr.grade_code != 'AB' AND m.module_code NOT LIKE 'ACLT%' AND m.module_code NOT LIKE 'CMSK%' AND m.module_code NOT LIKE 'MGMT%' THEN m.credit_value
                       ELSE 0
                   END), 0
               ),
               0
           ),
           2
       ) AS gpa_value
FROM student s
LEFT JOIN module_enrollment me ON me.student_student_no = s.student_no
LEFT JOIN results res ON res.module_enrollment_module_enrollment_id = me.module_enrollment_id
LEFT JOIN grade gr ON gr.grade_id = res.grade_grade_id
LEFT JOIN module m ON res.module_module_code = m.module_code
WHERE s.student_no = 'PS/2022/145' 
   OR s.student_no = 'PS/2022/147'
   OR s.student_no = 'PS/2022/157'
GROUP BY s.student_no
";

$result = Database::search($query1);
echo "New GPA Calculation (Ignoring exam_status='Completed'):\n";
while($row = $result->fetch_assoc()) {
    echo "  Student: " . $row['student_no'] . " | Calculated GPA: " . $row['gpa_value'] . "\n";
}
?>
