<?php
require_once 'connection/connection.php';
try {
    Database::setUpConnection();
    $res = Database::search("SELECT r.result_id, me.student_student_no, me.module_module_code, r.attempt_no FROM module_enrollment me LEFT JOIN results r ON me.module_enrollment_id = r.module_enrollment_module_enrollment_id WHERE me.student_student_no = 'PS/2022/091'");
    while ($row = $res->fetch_assoc()) {
        echo "Enrollment/Result: Student=" . $row['student_student_no'] . " | Module=" . $row['module_module_code'] . " | Attempt=" . $row['attempt_no'] . " | ResultID=" . $row['result_id'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
