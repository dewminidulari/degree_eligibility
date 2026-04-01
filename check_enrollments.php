<?php
require 'Connection/connection.php';
Database::setUpConnection();

echo "=== Students with module enrollments ===\n";
$res = Database::search('SELECT student_student_no, COUNT(*) as count FROM module_enrollment GROUP BY student_student_no LIMIT 5');
while($r = $res->fetch_assoc()) {
    echo $r['student_student_no'] . ': ' . $r['count'] . " modules\n";
}

echo "\n=== Test query with a student who has modules ===\n";
$res = Database::search("
    SELECT me.student_student_no, m.module_code, m.module_name, m.credit_value
    FROM module_enrollment me
    JOIN module m ON m.module_code = me.module_module_code
    LIMIT 10
");
while($r = $res->fetch_assoc()) {
    echo $r['student_student_no'] . " | " . $r['module_code'] . " | " . $r['credit_value'] . "\n";
}
?>
