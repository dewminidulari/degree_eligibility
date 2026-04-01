<?php
require 'Connection/connection.php';
Database::setUpConnection();

// Test the GPA calculation for a student
$student_no = 'PS/2022/047';

echo "=== MANUAL GPA CALCULATION FOR STUDENT: $student_no ===\n";

// Get all results for this student
$query = "
SELECT res.exam_status, gr.grade_code, gr.grade_point, m.credit_value, m.module_code
FROM module_enrollment me
LEFT JOIN results res ON res.module_enrollment_module_enrollment_id = me.module_enrollment_id
LEFT JOIN grade gr ON gr.grade_id = res.grade_grade_id
LEFT JOIN module m ON m.module_code = res.module_module_code
WHERE me.student_student_no = '$student_no'
ORDER BY m.module_code
";

$result = Database::search($query);

$total_points = 0;
$total_credits = 0;
$completed_count = 0;
$not_completed_count = 0;

echo "All Results:\n";
echo str_pad('Module', 15) . str_pad('Status', 15) . str_pad('Grade', 8) . str_pad('Points', 8) . str_pad('Credits', 8) . "\n";
echo str_repeat("-", 60) . "\n";

while ($row = $result->fetch_assoc()) {
    $status = $row['exam_status'] ?? 'NULL';
    $grade = $row['grade_code'] ?? 'NULL';
    $points = $row['grade_point'] ?? '0';
    $credits = $row['credit_value'] ?? '0';

    echo str_pad($row['module_code'] ?? 'NULL', 15)
         . str_pad($status, 15)
         . str_pad($grade, 8)
         . str_pad($points, 8)
         . str_pad($credits, 8) . "\n";

    if ($status === 'Completed') {
        $completed_count++;
        $total_points += floatval($points) * floatval($credits);
        $total_credits += floatval($credits);
    } else {
        $not_completed_count++;
    }
}

$calculated_gpa = $total_credits > 0 ? $total_points / $total_credits : 0;

echo "\n=== CALCULATION SUMMARY ===\n";
echo "Completed courses: $completed_count\n";
echo "Not completed courses: $not_completed_count\n";
echo "Total points: $total_points\n";
echo "Total credits: $total_credits\n";
echo "Calculated GPA: " . number_format($calculated_gpa, 2) . "\n\n";

// Now test the actual query from get_students.php
echo "=== ACTUAL QUERY RESULT ===\n";
$query2 = "
SELECT s.student_no,
       ROUND(
           COALESCE(
               SUM(CASE
                   WHEN res.exam_status = 'Completed' THEN gr.grade_point * m.credit_value
                   ELSE 0
               END) /
               NULLIF(
                   SUM(CASE
                       WHEN res.exam_status = 'Completed' THEN m.credit_value
                       ELSE 0
                   END), 0
               ),
               0
           ),
           2
       ) AS gpa_value
FROM student s
LEFT JOIN module_enrollment me ON me.student_student_no = s.student_no
LEFT JOIN results res ON res.module_enrollment_module_enrollment_id = me.module_enrollment_id
LEFT JOIN grade gr ON gr.grade_id = res.grade_grade_id
LEFT JOIN module m ON m.module_code = res.module_module_code
WHERE s.student_no = '$student_no'
GROUP BY s.student_no
";

$result2 = Database::search($query2);
$row2 = $result2->fetch_assoc();
echo "Query GPA result: " . ($row2['gpa_value'] ?? 'NULL') . "\n";
?>