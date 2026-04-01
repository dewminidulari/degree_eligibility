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
    WHERE me.student_student_no = 'PS/2022/047'
    ORDER BY m.module_code
";
$res = Database::search($query);
$totC = 0; $totP = 0;
while($r = $res->fetch_assoc()) {
    $isGpa = $r['is_gpa_module'] == 1;
    $grade = $r['grade_code'];
    $mod = $r['module_code'];
    $gp = floatval($r['grade_point']);
    $cr = intval($r['credit_value']);
    
    if(!$isGpa || $grade=='' || $grade=='NULL' || $grade=='AB') continue;
    if(strpos($mod,'ACLT')===0 || strpos($mod,'CMSK')===0 || strpos($mod,'MGMT')===0) continue;
    
    $totP += $gp * $cr;
    $totC += $cr;
    echo "$mod | $grade | pts($gp) * cred($cr) = " . ($gp * $cr) . "\n";
}
echo "TOTAL: Pts=$totP, Creds=$totC => " . ($totC > 0 ? round($totP/$totC, 2) : 0) . "\n";
?>
