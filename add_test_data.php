<?php
require 'Connection/connection.php';
Database::setUpConnection();

// Insert some test module enrollments if they don't exist
$testEnrollments = [
    ['2022-PS/2022/147', 1, 'PS/2022/147', 'COSC 21052'],
    ['2022-PS/2022/147', 1, 'PS/2022/147', 'COSC 21063'],
    ['2022-PS/2022/147', 1, 'PS/2022/147', 'PMAT 11001'],
    ['2022-PS/2022/147', 1, 'PS/2022/147', 'CMSK 14042'],
];

echo "Inserting test module enrollments...\n";
foreach ($testEnrollments as $enroll) {
    $query = "INSERT IGNORE INTO module_enrollment (acedemic_year_acedemic_year_id, student_student_no, module_module_code) 
              VALUES ({$enroll[1]}, '{$enroll[2]}', '{$enroll[3]}')";
    Database::iud($query);
}

// Now test the query with actual data
echo "\nTesting total credits calculation...\n";
$query = "SELECT s.student_no,
                 s.name_with_initial,
                 s.full_name,
                 p.program_name,
                 COALESCE(SUM(m.credit_value), 0) AS total_credits
          FROM student s
          LEFT JOIN program p ON s.program_program_id = p.program_id
          LEFT JOIN module_enrollment me ON me.student_student_no = s.student_no
          LEFT JOIN module m ON m.module_code = me.module_module_code
          WHERE s.student_no = 'PS/2022/147'
          GROUP BY s.student_no";

$result = Database::search($query);
if ($row = $result->fetch_assoc()) {
    echo json_encode($row, JSON_PRETTY_PRINT);
    echo "\nTotal Credits calculated: " . $row['total_credits'];
}

echo "\n\nDetails of enrolled modules for PS/2022/147:\n";
$query = "SELECT me.module_module_code, m.module_name, m.credit_value
          FROM module_enrollment me
          JOIN module m ON m.module_code = me.module_module_code
          WHERE me.student_student_no = 'PS/2022/147'";
$result = Database::search($query);
while ($row = $result->fetch_assoc()) {
    echo $row['module_module_code'] . " | " . $row['module_name'] . " | Credits: " . $row['credit_value'] . "\n";
}
?>
