<?php
require_once 'Connection/connection.php';

try {
    Database::setUpConnection();
    $student = 'PS/2022/147';
    $esc = mysqli_real_escape_string(Database::$connection, $student);
    $query = "
        SELECT m.module_code,
               m.module_name,
               m.credit_value,
               m.is_gpa_module,
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

    echo "All courses for $student:\n";
    foreach ($courses as $course) {
        echo $course['module_code'] . " - " . $course['grade_code'] . " - exam_status: " . $course['exam_status'] . " - credits: " . $course['credit_value'] . " - gpa_module: " . $course['is_gpa_module'] . " - grade_point: " . $course['grade_point'] . "\n";
    }

    // Now calculate GPA as per the logic
    $totalGradePoints = 0;
    $totalCredits = 0;

    foreach ($courses as $course) {
        $gradePoint = $course['grade_point'];
        $credits = $course['credit_value'];
        $isGpa = $course['is_gpa_module'];
        $examStatus = $course['exam_status'];
        $gradeCode = $course['grade_code'];

        // Exclusions: non-GPA modules, AB grade, not completed, ACLT or CMSK
        if ($isGpa == 0 || $gradeCode == 'AB' || $examStatus != 'Completed' || strpos($course['module_code'], 'ACLT') === 0 || strpos($course['module_code'], 'CMSK') === 0) {
            continue;
        }

        $totalGradePoints += $gradePoint * $credits;
        $totalCredits += $credits;
    }

    echo "\nTotal Grade Points: $totalGradePoints\n";
    echo "Total Credits: $totalCredits\n";
    echo "GPA: " . ($totalCredits > 0 ? $totalGradePoints / $totalCredits : 0) . "\n";

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
