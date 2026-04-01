<?php
require_once '../Connection/connection.php';
header('Content-Type: application/json');

// return a flattened list of students + basic academic info
// enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
try {
    // make sure the connection is ready
    Database::setUpConnection();

    $search = isset($_GET['search']) ? $_GET['search'] : '';
    
    // Query to get student info with total credits and calculated GPA from completed modules
    $query  = "SELECT s.student_no,
                      s.name_with_initial,
                      s.full_name,
                      s.admission_year,
                      p.program_id AS program_id,
                      p.program_name,
                      ed.ideligibility_for_degree,
                      ed.final_gpa,
                      ed.class_award_class_award_id,
                      ca.class_award_type AS db_classification,
                      ed.completion_year,
                      COALESCE(SUM(m.credit_value), 0) AS total_credits,
                      COALESCE(MAX(e.official_gpa), 0) AS database_gpa,
                      ROUND(
                          COALESCE(
                              SUM(CASE 
                                  WHEN m.is_gpa_module = 1 AND gr.grade_code IS NOT NULL AND gr.grade_code != 'AB' AND m.module_code NOT LIKE 'ACLT%' AND m.module_code NOT LIKE 'CMSK%' AND m.module_code NOT LIKE 'MGMT%' THEN gr.grade_point * m.credit_value
                                  ELSE 0
                              END) / 
                              NULLIF(SUM(CASE WHEN m.is_gpa_module = 1 AND gr.grade_code IS NOT NULL AND gr.grade_code != 'AB' AND m.module_code NOT LIKE 'ACLT%' AND m.module_code NOT LIKE 'CMSK%' AND m.module_code NOT LIKE 'MGMT%' THEN m.credit_value ELSE 0 END), 0),
                              0
                          ),
                          2
                      ) AS gpa_value
               FROM student s
             LEFT JOIN eligibility_degree ed
                 ON ed.student_student_no = s.student_no
             LEFT JOIN program p
                 ON p.program_id = COALESCE(ed.program_program_id, s.program_program_id)
                 LEFT JOIN class_award ca
                     ON ca.class_award_id = ed.class_award_class_award_id
               LEFT JOIN enrollment e
                      ON e.student_student_no = s.student_no
               LEFT JOIN module_enrollment me
                      ON me.student_student_no = s.student_no
               LEFT JOIN module m
                      ON m.module_code = me.module_module_code
               LEFT JOIN results res
                      ON res.module_enrollment_module_enrollment_id = me.module_enrollment_id
               LEFT JOIN grade gr
                      ON gr.grade_id = res.grade_grade_id
             ";

    if ($search !== '') {
        $esc = mysqli_real_escape_string(Database::$connection, $search);
        $query .= " WHERE p.program_name NOT IN ('ACLT', 'CMSK')
                    AND (s.student_no   LIKE '%$esc%'
                    OR s.name_with_initial LIKE '%$esc%'
                    OR s.full_name          LIKE '%$esc%'
                    OR p.program_name       LIKE '%$esc%')";
    } else {
        $query .= " WHERE p.program_name NOT IN ('ACLT', 'CMSK')";
    }
    
    $query .= " GROUP BY s.student_no, s.name_with_initial, s.full_name, s.admission_year,
                         p.program_id, p.program_name,
                         ed.ideligibility_for_degree, ed.final_gpa, ed.class_award_class_award_id, ca.class_award_type,
                         ed.completion_year
                ORDER BY s.student_no";

    $result = Database::search($query);
    if (!$result) {
        throw new Exception('Query failed: ' . Database::$connection->error);
    }

    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;   // send all columns through as JSON
    }

    echo json_encode(['success' => true, 'data' => $students]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
