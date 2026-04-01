<?php
require 'Connection/connection.php';
Database::setUpConnection();

$query = "SELECT me.module_module_code, gr.grade_code, m.credit_value, gr.grade_point, m.is_gpa_module 
FROM module_enrollment me 
LEFT JOIN results res ON me.module_enrollment_id = res.module_enrollment_module_enrollment_id 
LEFT JOIN grade gr ON res.grade_grade_id = gr.grade_id 
LEFT JOIN module m ON me.module_module_code = m.module_code
WHERE me.student_student_no = 'PS/2022/078'";

$res = Database::search($query);
$points_sql = 0; $credits_sql = 0;
while($r = $res->fetch_assoc()) {
    $is_gpa = $r['is_gpa_module'] == 1;
    $grade = $r['grade_code'];
    $mod = $r['module_module_code'];
    
    // Applying the same exclusion used in both:
    if ($is_gpa && $grade && $grade != 'AB' && $grade != 'NULL') {
        if (strpos($mod, 'ACLT') !== 0 && strpos($mod, 'CMSK') !== 0 && strpos($mod, 'MGMT') !== 0) {
            $pts = $r['grade_point'] * $r['credit_value'];
            $points_sql += $pts;
            $credits_sql += $r['credit_value'];
            echo "Module: $mod | Grade: $grade | Credits: {$r['credit_value']} | Point: {$r['grade_point']} | Total: $pts\n";
        }
    }
}
echo "SQL Total Points: $points_sql | SQL Total Credits: $credits_sql \n";
?>
