<?php
// Test the get_students.php API
$url = 'http://localhost/degree_eligibility/gui/get_students.php';
$response = file_get_contents($url);
$data = json_decode($response, true);

if ($data['success']) {
    echo "=== ALL STUDENTS ===\n";
    echo str_pad('Student No', 15) . str_pad('Name', 25) . str_pad('Program', 20) . str_pad('Year', 6) . str_pad('Credits', 10) . str_pad('GPA', 8) . str_pad('Eligible', 12) . "\n";
    echo str_repeat("-", 100) . "\n";

    foreach ($data['data'] as $student) {
        $eligible = $student['ideligibility_for_degree'] ? 'Yes' : 'No';
        echo str_pad($student['student_no'], 15)
             . str_pad(substr($student['name_with_initial'], 0, 24), 25)
             . str_pad(substr($student['program_name'], 0, 19), 20)
             . str_pad($student['year_of_study'] ?? '?', 6)
             . str_pad($student['total_credits'], 10)
             . str_pad(number_format($student['gpa_value'], 2), 8)
             . str_pad($eligible, 12) . "\n";
    }
} else {
    echo "API Error: " . $data['error'];
}
?>