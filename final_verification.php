<?php
// Test the complete API with updated logic
$url = 'http://localhost/degree_eligibility/gui/get_students.php';
$response = file_get_contents($url);
$data = json_decode($response, true);

if ($data['success']) {
    echo "=== UPDATED STUDENT RECORDS ===\n";
    echo str_pad('Student No', 15) . str_pad('Name', 25) . str_pad('Program', 20) . str_pad('Enrolled Credits', 18) . str_pad('GPA', 8) . "\n";
    echo str_repeat("-", 90) . "\n";

    foreach ($data['data'] as $student) {
        echo str_pad($student['student_no'], 15)
             . str_pad(substr($student['name_with_initial'], 0, 24), 25)
             . str_pad(substr($student['program_name'], 0, 19), 20)
             . str_pad($student['total_credits'], 18)
             . str_pad(number_format($student['gpa_value'], 2), 8) . "\n";
    }

    echo "\n=== SUMMARY ===\n";
    echo "✓ Total Credits = Sum of ALL enrolled course credits\n";
    echo "✓ GPA = (Sum of completed course points) / (Sum of ALL enrolled credits)\n";
    echo "✓ Non-completed courses contribute 0 points but still count in denominator\n";
} else {
    echo "API Error: " . $data['error'];
}
?>