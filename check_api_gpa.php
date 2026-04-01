<?php
// Test the get_students.php API
$url = 'http://localhost/degree_eligibility/gui/get_students.php';
$response = file_get_contents($url);
$data = json_decode($response, true);

function getNotEligibleReasons(array $student): array
{
    $reasons = [];

    $gpa = isset($student['gpa_value']) ? (float)$student['gpa_value'] : 0.0;
    $credits = isset($student['total_credits']) ? (float)$student['total_credits'] : 0.0;
    $admissionYear = isset($student['admission_year']) ? (int)$student['admission_year'] : 0;
    $completionYear = isset($student['completion_year']) ? (int)$student['completion_year'] : 0;

    // Basic eligibility checks from the fields returned by get_students.php
    if ($credits < 90) {
        $reasons[] = "Insufficient credits ($credits/90)";
    }

    if ($gpa < 2.00) {
        $reasons[] = 'GPA below minimum (2.00)';
    }

    if ($admissionYear > 0 && $completionYear > 0) {
        $duration = $completionYear - $admissionYear;
        if ($duration > 5) {
            $reasons[] = "Duration exceeds limit ({$duration} years > 5 years)";
        }
    }

    return $reasons;
}

if ($data['success']) {
    echo "=== STUDENTS WITH GPA 4.00 ===\n";
    $count = 0;
    foreach ($data['data'] as $student) {
        if (floatval($student['gpa_value']) == 4.00) {
            echo "Student: {$student['student_no']} - {$student['name_with_initial']} | GPA: {$student['gpa_value']} | Credits: {$student['total_credits']}\n";
            $count++;
            if ($count >= 5) break;
        }
    }

    if ($count == 0) {
        echo "No students found with GPA 4.00\n";
    }

    echo "\n=== SAMPLE OF ALL STUDENTS ===\n";
    $count2 = 0;
    foreach ($data['data'] as $student) {
        echo "Student: {$student['student_no']} - {$student['name_with_initial']} | GPA: {$student['gpa_value']} | Credits: {$student['total_credits']}\n";
        $count2++;
        if ($count2 >= 10) break;
    }

    echo "\n=== NOT ELIGIBLE STUDENTS WITH REASONS ===\n";
    $count3 = 0;
    foreach ($data['data'] as $student) {
        $reasons = getNotEligibleReasons($student);
        if (count($reasons) === 0) {
            continue;
        }

        $reasonText = implode('; ', $reasons);
        echo "Student: {$student['student_no']} - {$student['name_with_initial']} | GPA: {$student['gpa_value']} | Credits: {$student['total_credits']} | Reason: {$reasonText}\n";
        $count3++;
        if ($count3 >= 20) break;
    }

    if ($count3 === 0) {
        echo "No ineligible students found by current query-based rules.\n";
    }
} else {
    echo "API Error: " . $data['error'];
}
?>