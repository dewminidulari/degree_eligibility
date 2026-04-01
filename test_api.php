<?php
require_once 'Connection/connection.php';
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    Database::setUpConnection();
    
    // Test the updated query
    $query = "SELECT s.student_no,
                     s.name_with_initial,
                     s.full_name,
                     s.admission_year,
                     p.program_id AS program_id,
                     p.program_name,
                     e.year_of_study,
                     g.gpa_value,
                     ed.ideligibility_for_degree,
                     ed.final_gpa,
                     ed.class_award_class_award_id,
                     ed.completion_year,
                     COALESCE(SUM(m.credit_value), 0) AS total_credits
              FROM student s
              LEFT JOIN program p ON s.program_program_id = p.program_id
              LEFT JOIN enrollment e ON e.student_student_no = s.student_no
              LEFT JOIN gpa g ON g.student_student_no = s.student_no
              LEFT JOIN module_enrollment me ON me.student_student_no = s.student_no
              LEFT JOIN module m ON m.module_code = me.module_module_code
              LEFT JOIN eligibility_degree ed ON ed.student_student_no = s.student_no
              GROUP BY s.student_no, s.name_with_initial, s.full_name, s.admission_year,
                       p.program_id, p.program_name, e.year_of_study, g.gpa_value,
                       ed.ideligibility_for_degree, ed.final_gpa, ed.class_award_class_award_id,
                       ed.completion_year
              ORDER BY s.student_no
              LIMIT 5";
    
    $result = Database::search($query);
    
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    
    echo json_encode(['success' => true, 'count' => count($students), 'data' => $students], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
