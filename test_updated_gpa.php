<?php
require 'Connection/connection.php';
Database::setUpConnection();

// Test the updated GPA calculation
$query = "
SELECT s.student_no, s.name_with_initial,
       COALESCE(SUM(m.credit_value), 0) AS total_enrolled_credits,
       ROUND(
           COALESCE(
               SUM(CASE 
                   WHEN res.exam_status = 'Completed' THEN gr.grade_point * m.credit_value
                   ELSE 0
               END) / 
               NULLIF(SUM(m.credit_value), 0),
               0
           ),
           2
       ) AS calculated_gpa
FROM student s
LEFT JOIN module_enrollment me ON me.student_student_no = s.student_no
LEFT JOIN module m ON m.module_code = me.module_module_code
LEFT JOIN results res ON res.module_enrollment_module_enrollment_id = me.module_enrollment_id
LEFT JOIN grade gr ON gr.grade_id = res.grade_grade_id
GROUP BY s.student_no, s.name_with_initial
ORDER BY s.student_no
";

$result = Database::search($query);

echo "=== UPDATED GPA CALCULATION TEST ===\n";
echo str_pad('Student No', 15) . str_pad('Name', 25) . str_pad('Enrolled Credits', 18) . str_pad('GPA', 8) . "\n";
echo str_repeat("-", 70) . "\n";

while ($row = $result->fetch_assoc()) {
    echo str_pad($row['student_no'], 15)
         . str_pad(substr($row['name_with_initial'], 0, 24), 25)
         . str_pad($row['total_enrolled_credits'], 18)
         . str_pad(number_format($row['calculated_gpa'], 2), 8) . "\n";
}

// Detailed breakdown for one student
echo "\n=== DETAILED BREAKDOWN FOR PS/2022/145 ===\n";
$query2 = "
SELECT m.module_code, m.module_name, m.credit_value, res.exam_status, gr.grade_code, gr.grade_point
FROM module_enrollment me
LEFT JOIN module m ON m.module_code = me.module_module_code
LEFT JOIN results res ON res.module_enrollment_module_enrollment_id = me.module_enrollment_id
LEFT JOIN grade gr ON gr.grade_id = res.grade_grade_id
WHERE me.student_student_no = 'PS/2022/145'
ORDER BY m.module_code
";

$result2 = Database::search($query2);

$total_enrolled_credits = 0;
$total_completed_points = 0;

echo "\nModules:\n";
echo str_pad('Code', 12) . str_pad('Status', 15) . str_pad('Grade', 8) . str_pad('Credits', 8) . str_pad('Points', 8) . "\n";
echo str_repeat("-", 55) . "\n";

while ($row2 = $result2->fetch_assoc()) {
    $status = $row2['exam_status'] ?? 'NULL';
    $grade = $row2['grade_code'] ?? 'NULL';
    $points = $row2['grade_point'] ?? '0';
    $credits = $row2['credit_value'] ?? '0';

    echo str_pad($row2['module_code'] ?? 'NULL', 12)
         . str_pad($status, 15)
         . str_pad($grade, 8)
         . str_pad($credits, 8)
         . str_pad($points, 8) . "\n";

    $total_enrolled_credits += floatval($credits);
    if ($status === 'Completed') {
        $total_completed_points += floatval($points) * floatval($credits);
    }
}

$expected_gpa = $total_enrolled_credits > 0 ? $total_completed_points / $total_enrolled_credits : 0;

echo "\n=== CALCULATION ===\n";
echo "Total enrolled credits: $total_enrolled_credits\n";
echo "Total completed points: $total_completed_points\n";
echo "Expected GPA: " . number_format($expected_gpa, 2) . "\n";
?>