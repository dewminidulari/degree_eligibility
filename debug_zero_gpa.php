<?php
require 'Connection/connection.php';
Database::setUpConnection();

echo "=== STUDENTS WITH NO COMPLETED COURSES ===\n";

// Find students with zero completed courses
$query = "
SELECT s.student_no, s.name_with_initial,
       COUNT(CASE WHEN res.exam_status = 'Completed' THEN 1 END) as completed_count,
       COUNT(res.result_id) as total_results,
       COUNT(DISTINCT me.module_enrollment_id) as total_enrollments
FROM student s
LEFT JOIN module_enrollment me ON me.student_student_no = s.student_no
LEFT JOIN results res ON res.module_enrollment_module_enrollment_id = me.module_enrollment_id
GROUP BY s.student_no, s.name_with_initial
HAVING completed_count = 0 AND total_enrollments > 0
LIMIT 5
";

$result = Database::search($query);
$students = [];

while ($row = $result->fetch_assoc()) {
    echo "Student: {$row['student_no']} - {$row['name_with_initial']} | Enrollments: {$row['total_enrollments']} | Completed: {$row['completed_count']} | Results: {$row['total_results']}\n";
    $students[] = $row['student_no'];
}

// Test the GPA for these students
if (!empty($students)) {
    foreach ($students as $student_no) {
        echo "\n=== TESTING STUDENT: $student_no ===\n";
        
        // Get all results for this student
        $query2 = "
        SELECT res.exam_status, gr.grade_code, gr.grade_point, m.credit_value, m.module_code
        FROM module_enrollment me
        LEFT JOIN results res ON res.module_enrollment_module_enrollment_id = me.module_enrollment_id
        LEFT JOIN grade gr ON gr.grade_id = res.grade_grade_id
        LEFT JOIN module m ON m.module_code = res.module_module_code
        WHERE me.student_student_no = '$student_no'
        ORDER BY m.module_code
        ";

        $result2 = Database::search($query2);

        echo "Courses:\n";
        echo str_pad('Module', 15) . str_pad('Status', 15) . str_pad('Grade', 8) . str_pad('Points', 8) . str_pad('Credits', 8) . "\n";
        echo str_repeat("-", 60) . "\n";

        while ($row2 = $result2->fetch_assoc()) {
            $status = $row2['exam_status'] ?? 'NULL';
            $grade = $row2['grade_code'] ?? 'NULL';
            $points = $row2['grade_point'] ?? '0';
            $credits = $row2['credit_value'] ?? '0';

            echo str_pad($row2['module_code'] ?? 'NULL', 15)
                 . str_pad($status, 15)
                 . str_pad($grade, 8)
                 . str_pad($points, 8)
                 . str_pad($credits, 8) . "\n";
        }

        // Test the actual query result
        echo "\n=== API QUERY RESULT ===\n";
        $query3 = "
        SELECT s.student_no,
               COALESCE(SUM(CASE WHEN res.exam_status = 'Completed' THEN m.credit_value ELSE 0 END), 0) AS total_credits,
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

        $result3 = Database::search($query3);
        $row3 = $result3->fetch_assoc();
        echo "Total Credits: " . ($row3['total_credits'] ?? 'NULL') . "\n";
        echo "GPA: " . ($row3['gpa_value'] ?? 'NULL') . "\n";
    }
}
?>