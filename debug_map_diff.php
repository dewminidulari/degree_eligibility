<?php
require 'Connection/connection.php';
Database::setUpConnection();
$query = "
    SELECT m.module_code,
           m.credit_value,
           m.is_gpa_module,
           IFNULL(g.grade_code, '') AS grade_code,
           IFNULL(g.grade_point, 0) AS grade_point
    FROM module_enrollment me
    LEFT JOIN module m ON m.module_code = me.module_module_code
    LEFT JOIN results r ON r.module_enrollment_module_enrollment_id = me.module_enrollment_id
    LEFT JOIN grade g ON g.grade_id = r.grade_grade_id
    WHERE me.student_student_no = 'PS/2022/078'
    ORDER BY m.module_code
";
$res = Database::search($query);

$map = ['A+'=>4.0,'A'=>4.0,'A-'=>3.7,'B+'=>3.3,'B'=>3.0,'B-'=>2.7,'C+'=>2.3,'C'=>2.0,'C-'=>1.7,'D+'=>1.3,'D'=>1.0,'E'=>0.0];

$totC = 0; $totP_db = 0; $totP_map = 0;
while($r = $res->fetch_assoc()) {
    $isGpa = $r['is_gpa_module'] == 1;
    $grade = trim($r['grade_code']);
    $mod = trim($r['module_code']);
    $gp_db = floatval($r['grade_point']);
    $cr = intval($r['credit_value']);
    
    if(!$isGpa || $grade=='' || $grade=='NULL' || $grade=='AB') continue;
    if(strpos($mod,'ACLT')===0 || strpos($mod,'CMSK')===0 || strpos($mod,'MGMT')===0) continue;
    
    $gp_map = isset($map[$grade]) ? $map[$grade] : $gp_db;
    
    $totP_db += $gp_db * $cr;
    $totP_map += $gp_map * $cr;
    $totC += $cr;
}
echo "DB POINTS: $totP_db => " . round($totP_db/$totC, 2) . "\n";
echo "MAP POINTS: $totP_map => " . round($totP_map/$totC, 2) . "\n";
?>
