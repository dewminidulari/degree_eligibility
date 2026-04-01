<?php
require 'Connection/connection.php';
Database::setUpConnection();

// Check if there are results with "AB" grade and "Completed" status
$query = "
SELECT s.student_no, s.name_with_initial, res.exam_status, gr.grade_code, gr.grade_point, m.module_code
FROM student s
LEFT JOIN module_enrollment me ON me.student_student_no = s.student_no
LEFT JOIN results res ON res.module_enrollment_module_enrollment_id = me.module_enrollment_id
LEFT JOIN grade gr ON gr.grade_id = res.grade_grade_id
LEFT JOIN module m ON m.module_code = res.module_module_code
WHERE gr.grade_code = 'AB' AND res.exam_status = 'Completed'
LIMIT 10
";

$result = Database::search($query);

echo "=== RESULTS WITH AB GRADE AND COMPLETED STATUS ===\n";
while ($row = $result->fetch_assoc()) {
    echo "Student: {$row['student_no']} - {$row['name_with_initial']} | Module: {$row['module_code']} | Status: {$row['exam_status']} | Grade: {$row['grade_code']} | Points: {$row['grade_point']}\n";
}

// Check if there are students who only have AB grades
$query2 = "
SELECT s.student_no, s.name_with_initial,
       GROUP_CONCAT(DISTINCT gr.grade_code) as all_grades,
       COUNT(CASE WHEN gr.grade_code = 'AB' THEN 1 END) as ab_count,
       COUNT(res.result_id) as total_results
FROM student s
LEFT JOIN module_enrollment me ON me.student_student_no = s.student_no
LEFT JOIN results res ON res.module_enrollment_module_enrollment_id = me.module_enrollment_id
LEFT JOIN grade gr ON gr.grade_id = res.grade_grade_id
WHERE res.exam_status = 'Completed'
GROUP BY s.student_no, s.name_with_initial
HAVING ab_count > 0 AND ab_count = total_results
LIMIT 5
";

$result2 = Database::search($query2);

echo "\n=== STUDENTS WITH ONLY AB GRADES ===\n";
while ($row2 = $result2->fetch_assoc()) {
    echo "Student: {$row2['student_no']} - {$row2['name_with_initial']} | Grades: {$row2['all_grades']} | AB Count: {$row2['ab_count']} | Total: {$row2['total_results']}\n";

    // Calculate GPA for this student
    $query3 = "
    SELECT ROUND(
        COALESCE(
            SUM(CASE WHEN res.exam_status = 'Completed' THEN gr.grade_point * m.credit_value ELSE 0 END) /
            NULLIF(SUM(CASE WHEN res.exam_status = 'Completed' THEN m.credit_value ELSE 0 END), 0),
            0
        ), 2) as gpa_value
    FROM module_enrollment me
    LEFT JOIN results res ON res.module_enrollment_module_enrollment_id = me.module_enrollment_id
    LEFT JOIN grade gr ON gr.grade_id = res.grade_grade_id
    LEFT JOIN module m ON m.module_code = res.module_module_code
    WHERE me.student_student_no = '{$row2['student_no']}'
    ";

    $result3 = Database::search($query3);
    $row3 = $result3->fetch_assoc();
    echo "  GPA: " . ($row3['gpa_value'] ?? 'NULL') . "\n\n";
}
?>