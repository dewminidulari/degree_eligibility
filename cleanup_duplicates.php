<?php
require_once 'connection/connection.php';
try {
    Database::setUpConnection();
    // Delete duplicate results first
    Database::$connection->query("
        DELETE r FROM results r
        JOIN module_enrollment e1 ON r.module_enrollment_module_enrollment_id = e1.module_enrollment_id
        JOIN module_enrollment e2 ON e1.student_student_no = e2.student_student_no AND e1.module_module_code = e2.module_module_code
        WHERE e1.module_enrollment_id > e2.module_enrollment_id
    ");

    // Delete duplicate module enrollments (keep the one with the lowest ID)
    Database::$connection->query("
        DELETE e1 FROM module_enrollment e1
        JOIN module_enrollment e2 ON e1.student_student_no = e2.student_student_no AND e1.module_module_code = e2.module_module_code
        WHERE e1.module_enrollment_id > e2.module_enrollment_id 
    ");
    
    // Cleanup Academic Years that are just numbers if they aren't used anymore
    // (Optional, just keeping it clean)
    Database::$connection->query("
        DELETE FROM acedemic_year 
        WHERE year_label IN ('1', '2', '3', '4') 
        AND acedemic_year_id NOT IN (SELECT acedemic_year_acedemic_year_id FROM module_enrollment)
    ");
    echo "Cleanup complete without foreign key errors.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
