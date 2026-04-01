<?php
require_once '../Connection/connection.php';
header('Content-Type: application/json');

// returns list of modules a student has enrolled along with grades
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    Database::setUpConnection();
    $student = isset($_GET['student_no']) ? $_GET['student_no'] : '';
    if ($student === '') {
        throw new Exception('Missing student_no parameter');
    }

    $esc = mysqli_real_escape_string(Database::$connection, $student);
    $query = "
        SELECT m.module_code,
               m.module_name,
               m.credit_value,
               m.is_gpa_module,
             m.module_status,
               IFNULL(r.exam_status, '') AS exam_status,
               IFNULL(g.grade_code, '') AS grade_code,
               IFNULL(g.grade_point, 0) AS grade_point
        FROM module_enrollment me
        LEFT JOIN module m ON m.module_code = me.module_module_code
        LEFT JOIN results r ON r.module_enrollment_module_enrollment_id = me.module_enrollment_id
        LEFT JOIN grade g ON g.grade_id = r.grade_grade_id
        WHERE me.student_student_no = '$esc'
        ORDER BY m.module_code
    ";

    $res = Database::search($query);
    if (!$res) {
        throw new Exception('Query failed: ' . Database::$connection->error);
    }

    $courses = [];
    while ($row = $res->fetch_assoc()) {
        $courses[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $courses]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>