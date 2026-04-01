<?php
require 'Connection/connection.php';
Database::setUpConnection();
$query = "
    SELECT m.module_code,
           r.exam_status,
           m.credit_value,
           g.grade_code,
           g.grade_point
    FROM module_enrollment me
    LEFT JOIN module m ON m.module_code = me.module_module_code
    LEFT JOIN results r ON r.module_enrollment_module_enrollment_id = me.module_enrollment_id
    LEFT JOIN grade g ON g.grade_id = r.grade_grade_id
    WHERE me.student_student_no = 'PS/2022/078'
";
$res = Database::search($query);

$map = ['A+'=>4.0,'A'=>4.0,'A-'=>3.7,'B+'=>3.3,'B'=>3.0,'B-'=>2.7,'C+'=>2.3,'C'=>2.0,'C-'=>1.7,'D+'=>1.3,'D'=>1.0,'E'=>0.0];

while($r = $res->fetch_assoc()) {
    $gc = $r['grade_code'] ?? '';
    // This is how dashboard.js reads it:
    $gc_js = $gc; 
    $gp_js = isset($map[$gc_js]) ? $map[$gc_js] : 0;
    
    $gp_db = floatval($r['grade_point']);
    
    if ($gp_js != $gp_db) {
        echo "MISMATCH: Module {$r['module_code']} | Grade: '{$gc}' | DB: {$gp_db} | JS: {$gp_js}\n";
    }
}
?>
