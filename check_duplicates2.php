<?php
require_once 'connection/connection.php';
try {
    Database::setUpConnection();
    // find duplicates in module_enrollment
    $res = Database::search("
        SELECT student_student_no, module_module_code, COUNT(*) as cnt 
        FROM module_enrollment 
        GROUP BY student_student_no, module_module_code 
        HAVING cnt > 1
    ");
    $found_dup = false;
    while ($row = $res->fetch_assoc()) {
        $found_dup = true;
        echo "Duplicate: Student=" . $row['student_student_no'] . " | Module=" . $row['module_module_code'] . " | Count=" . $row['cnt'] . "\n";
    }
    if (!$found_dup) {
        echo "No duplicates found in module_enrollment based on student and module.\n";
    }

    // find duplicates in results for same enrollment
    $res2 = Database::search("
        SELECT module_enrollment_module_enrollment_id, COUNT(*) as cnt 
        FROM results 
        GROUP BY module_enrollment_module_enrollment_id 
        HAVING cnt > 1
    ");
    $found_dup2 = false;
    while ($row = $res2->fetch_assoc()) {
        $found_dup2 = true;
        echo "Duplicate Result for enrollment ID=" . $row['module_enrollment_module_enrollment_id'] . " | Count=" . $row['cnt'] . "\n";
    }
    if (!$found_dup2) {
        echo "No duplicate results for the same enrollment ID.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
