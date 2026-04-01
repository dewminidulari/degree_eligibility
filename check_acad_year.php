<?php
require_once 'connection/connection.php';
try {
    Database::setUpConnection();
    // list all academic years
    $res = Database::search("SELECT * FROM acedemic_year");
    echo "Academic Years:\n";
    while ($row = $res->fetch_assoc()) {
        echo "ID=" . $row['acedemic_year_id'] . " | Label='" . $row['year_label'] . "'\n";
    }
    echo "\nModule Enrollments for PS/2022/091:\n";
    $res = Database::search("SELECT * FROM module_enrollment WHERE student_student_no='PS/2022/091'");
    while ($row = $res->fetch_assoc()) {
        echo "EnrollmentID=" . $row['module_enrollment_id'] . " | AcadYearID=" . $row['acedemic_year_acedemic_year_id'] . " | Module=" . $row['module_module_code'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
