<?php
require 'Connection/connection.php';
Database::setUpConnection();

$studentNo = 'PS/2022/078';
$modules = ['COSC 21052', 'COSC 21063', 'PMAT 11001'];

// Insert more modules
foreach ($modules as $module) {
    Database::iud("INSERT IGNORE INTO module_enrollment (acedemic_year_acedemic_year_id, student_student_no, module_module_code) 
                   VALUES (1, '$studentNo', '$module')");
}

// Verify total calculation
echo "After adding more modules:\n\n";
$result = Database::search("
    SELECT s.student_no,
           COALESCE(SUM(m.credit_value), 0) as total_credits
    FROM student s
    LEFT JOIN module_enrollment me ON me.student_student_no = s.student_no
    LEFT JOIN module m ON m.module_code = me.module_module_code
    WHERE s.student_no = '$studentNo'
    GROUP BY s.student_no
");

if ($row = $result->fetch_assoc()) {
    echo "Student: " . $row['student_no'] . "\n";
    echo "Total Credits: " . $row['total_credits'] . "\n";
}

echo "\nEnrolled modules:\n";
$result = Database::search("
    SELECT me.module_module_code, m.module_name, m.credit_value
    FROM module_enrollment me
    JOIN module m ON m.module_code = me.module_module_code
    WHERE me.student_student_no = '$studentNo'
");

$sum = 0;
while ($row = $result->fetch_assoc()) {
    echo "• " . $row['module_module_code'] . " | " . $row['module_name'] . " | " . $row['credit_value'] . " credits\n";
    $sum += intval($row['credit_value']);
}
echo "\nManual Sum: " . $sum . "\n";
?>
