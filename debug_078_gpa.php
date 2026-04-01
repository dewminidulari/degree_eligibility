<?php
require 'Connection/connection.php';
Database::setUpConnection();

$query = "SELECT s.student_no, COUNT(e.enrollment_id) as enroll_count, COUNT(me.module_enrollment_id) as mod_count
FROM student s
LEFT JOIN enrollment e ON e.student_student_no = s.student_no
LEFT JOIN module_enrollment me ON me.student_student_no = s.student_no
WHERE s.student_no = 'PS/2022/078'
GROUP BY s.student_no";
$res = Database::search($query);
echo "Cartesian Test:\n";
while($r = $res->fetch_assoc()) echo json_encode($r)."\n";

$query2 = "SELECT me.module_module_code, gr.grade_code, m.credit_value, gr.grade_point
FROM module_enrollment me 
LEFT JOIN results res ON me.module_enrollment_id = res.module_enrollment_module_enrollment_id 
LEFT JOIN grade gr ON res.grade_grade_id = gr.grade_id 
LEFT JOIN module m ON me.module_module_code = m.module_code
WHERE me.student_student_no = 'PS/2022/078'";
$res2 = Database::search($query2);
$p = 0; $c = 0;
while($r = $res2->fetch_assoc()) {
    if ($r['grade_code'] && $r['grade_code'] != 'AB' && strpos($r['module_module_code'],'ACLT')!==0 && strpos($r['module_module_code'],'CMSK')!==0 && strpos($r['module_module_code'],'MGMT')!==0) {
        $p += $r['grade_point'] * $r['credit_value'];
        $c += $r['credit_value'];
    }
}
echo "Manual SQL Result: Points: $p, Credits: $c, GPA: " . ($c>0 ? round($p/$c, 2) : 0) . "\n";
?>
