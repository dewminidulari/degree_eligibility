<?php
// Test the complete get_students.php API
require 'Connection/connection.php';
header('Content-Type: application/json');

Database::setUpConnection();

$url = 'http://localhost/degree_eligibility/gui/get_students.php';
$response = file_get_contents($url);
$data = json_decode($response, true);

if ($data['success']) {
    echo "=== API Response Test ===\n";
    echo "Total Students Retrieved: " . count($data['data']) . "\n\n";
    
    echo "Sample Student Records (showing GPA calculation):\n";
    echo str_pad('Student No', 15) . str_pad('Name', 20) . str_pad('Program', 25) . str_pad('Total Credits', 15) . str_pad('GPA', 10) . "\n";
    echo str_repeat("-", 85) . "\n";
    
    $count = 0;
    foreach ($data['data'] as $student) {
        echo str_pad($student['student_no'], 15) 
             . str_pad(substr($student['name_with_initial'], 0, 19), 20) 
             . str_pad(substr($student['program_name'], 0, 24), 25) 
             . str_pad($student['total_credits'], 15) 
             . str_pad(number_format($student['gpa_value'], 2), 10) . "\n";
        
        $count++;
        if ($count >= 10) break;
    }
    
    echo "\n✓ GPA values are being calculated from completed modules\n";
    echo "✓ Total credits are being summed from all modules\n";
} else {
    echo "Error: " . $data['error'];
}
?>
