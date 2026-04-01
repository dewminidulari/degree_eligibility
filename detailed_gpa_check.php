<?php
require 'Connection/connection.php';
Database::setUpConnection();

// Test each student with detailed breakdown
$query_all = "
SELECT s.student_no, s.name_with_initial
FROM student s
GROUP BY s.student_no, s.name_with_initial
LIMIT 3
";

$result = Database::search($query_all);
while ($row = $result->fetch_assoc()) {
    $student_no = $row['student_no'];
    echo "\n\n=== DETAILED BREAKDOWN FOR STUDENT: $student_no ===\n";

    // Get all modules with their status
    $query_detailed = "
    SELECT 
        m.module_code, 
        m.module_name,
        m.credit_value,
        res.exam_status,
        gr.grade_code,
        gr.grade_point,
        (gr.grade_point * m.credit_value) as weighted_points
    FROM module_enrollment me
    LEFT JOIN module m ON m.module_code = me.module_module_code
    LEFT JOIN results res ON res.module_enrollment_module_enrollment_id = me.module_enrollment_id
    LEFT JOIN grade gr ON gr.grade_id = res.grade_grade_id
    WHERE me.student_student_no = '$student_no'
    ORDER BY m.module_code
    ";

    $result2 = Database::search($query_detailed);
    
    $total_completed_points = 0;
    $total_completed_credits = 0;
    $total_all_credits = 0;
    
    echo "\nModules:\n";
    echo str_pad('Code', 12) . str_pad('Status', 15) . str_pad('Grade', 8) . str_pad('Credits', 8) . str_pad('Points', 8) . str_pad('Weighted', 10) . "\n";
    echo str_repeat("-", 70) . "\n";

    while ($row2 = $result2->fetch_assoc()) {
        $is_completed = ($row2['exam_status'] === 'Completed');
        $status = $row2['exam_status'] ?? 'NULL';
        $grade = $row2['grade_code'] ?? 'NULL';
        $points = $row2['grade_point'] ?? '0';
        $credits = $row2['credit_value'] ?? '0';
        $weighted = $row2['weighted_points'] ?? '0';

        $display_status = $is_completed ? '✓ ' . $status : '✗ ' . $status;
        
        echo str_pad($row2['module_code'] ?? 'NULL', 12)
             . str_pad($display_status, 15)
             . str_pad($grade, 8)
             . str_pad($credits, 8)
             . str_pad($points, 8)
             . str_pad(number_format($weighted, 2), 10) . "\n";

        if ($is_completed) {
            $total_completed_points += floatval($weighted);
            $total_completed_credits += floatval($credits);
        }
        $total_all_credits += floatval($credits);
    }

    $calculated_gpa = $total_completed_credits > 0 ? $total_completed_points / $total_completed_credits : 0;
    
    echo "\n=== CALCULATIONS ===\n";
    echo "Total completed points: " . number_format($total_completed_points, 2) . "\n";
    echo "Total completed credits: " . number_format($total_completed_credits, 2) . "\n";
    echo "Calculated GPA: " . number_format($calculated_gpa, 2) . "\n";
    echo "Total all credits: " . number_format($total_all_credits, 2) . "\n";

    // Check what API returns
    $query_api = "
    SELECT 
        COALESCE(SUM(CASE WHEN res.exam_status = 'Completed' THEN m.credit_value ELSE 0 END), 0) AS api_credits,
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
        ) AS api_gpa
    FROM module_enrollment me
    LEFT JOIN results res ON res.module_enrollment_module_enrollment_id = me.module_enrollment_id
    LEFT JOIN grade gr ON gr.grade_id = res.grade_grade_id
    LEFT JOIN module m ON m.module_code = res.module_module_code
    WHERE me.student_student_no = '$student_no'
    ";

    $result3 = Database::search($query_api);
    $row3 = $result3->fetch_assoc();
    
    echo "\n=== API VALUES ===\n";
    echo "API Credits: " . ($row3['api_credits'] ?? 'NULL') . "\n";
    echo "API GPA: " . ($row3['api_gpa'] ?? 'NULL') . "\n";
    echo "Match: " . (abs(floatval($row3['api_gpa']) - $calculated_gpa) < 0.01 ? '✓ YES' : '✗ NO') . "\n";
}
?>