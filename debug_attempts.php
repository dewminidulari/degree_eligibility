<?php
require 'Connection/connection.php';
Database::setUpConnection();
$query = "
    SELECT m.module_code,
           r.attempt_no,
           r.exam_status,
           m.credit_value,
           g.grade_code,
           g.grade_point
    FROM module_enrollment me
    LEFT JOIN module m ON m.module_code = me.module_module_code
    LEFT JOIN results r ON r.module_enrollment_module_enrollment_id = me.module_enrollment_id
    LEFT JOIN grade g ON g.grade_id = r.grade_grade_id
    WHERE me.student_student_no = 'PS/2022/078'
    ORDER BY m.module_code
";
$res = Database::search($query);

while($r = $res->fetch_assoc()) {
    echo json_encode($r) . "\n";
}
?>
