<?php
require 'Connection/connection.php';
Database::setUpConnection();

$studentNo = 'PS/2022/078';

// First, check what modules are available
echo "Available modules:\n";
$result = Database::search("SELECT module_code, module_name, credit_value FROM module LIMIT 10");
$modules = [];
while ($row = $result->fetch_assoc()) {
    echo $row['module_code'] . " | " . $row['module_name'] . " | Credits: " . $row['credit_value'] . "\n";
    $modules[] = $row['module_code'];
}

// Insert module enrollments for this student
echo "\nAdding module enrollments for $studentNo...\n";
foreach (array_slice($modules, 0, 4) as $moduleCode) {
    $query = "INSERT IGNORE INTO module_enrollment (acedemic_year_acedemic_year_id, student_student_no, module_module_code) 
              VALUES (1, '$studentNo', '$moduleCode')";
    Database::iud($query);
}

// Now test the query with actual data
echo "\nTesting total credits calculation for $studentNo...\n";
$query = "SELECT s.student_no,
                 s.name_with_initial,
                 s.full_name,
                 p.program_name,
                 COALESCE(SUM(m.credit_value), 0) AS total_credits
          FROM student s
          LEFT JOIN program p ON s.program_program_id = p.program_id
          LEFT JOIN module_enrollment me ON me.student_student_no = s.student_no
          LEFT JOIN module m ON m.module_code = me.module_module_code
          WHERE s.student_no = '$studentNo'
          GROUP BY s.student_no";

$result = Database::search($query);
if ($row = $result->fetch_assoc()) {
    echo json_encode($row, JSON_PRETTY_PRINT);
    echo "\n✓ Total Credits calculated: " . $row['total_credits'];
}

echo "\n\nDetails of enrolled modules for $studentNo:\n";
$query = "SELECT me.module_module_code, m.module_name, m.credit_value
          FROM module_enrollment me
          JOIN module m ON m.module_code = me.module_module_code
          WHERE me.student_student_no = '$studentNo'";
$result = Database::search($query);
$total = 0;
while ($row = $result->fetch_assoc()) {
    echo $row['module_module_code'] . " | " . $row['module_name'] . " | Credits: " . $row['credit_value'] . "\n";
    $total += $row['credit_value'];
}
echo "Sum: $total\n";
?>
