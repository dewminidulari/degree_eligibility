<?php
require 'Connection/connection.php';
Database::setUpConnection();

$student_no = 'PS/2022/145'; // Or any test student that has data
echo "Testing eligibility rules for $student_no\n";

$query = "
SELECT m.module_code, m.credit_value, gr.grade_code, gr.grade_point, res.exam_status
FROM module_enrollment me
LEFT JOIN results res ON me.module_enrollment_id = res.module_enrollment_module_enrollment_id
LEFT JOIN grade gr ON res.grade_grade_id = gr.grade_id
LEFT JOIN module m ON me.module_module_code = m.module_code
WHERE me.student_student_no = '$student_no'
";

$result = Database::search($query);

$modules = [];
while ($row = $result->fetch_assoc()) {
    if (!empty($row['module_code']) && !empty($row['grade_code']) && $row['grade_code'] != 'AB') {
        $subject = substr(trim($row['module_code']), 0, 4);
        $year = intval(substr(trim($row['module_code']), 4, 1));
        $is_practical = (substr(trim($row['module_code']), -1) === '1');
        
        $modules[] = [
            'code' => $row['module_code'],
            'credits' => intval($row['credit_value']),
            'grade' => trim($row['grade_code']),
            'points' => floatval($row['grade_point']),
            'subject' => $subject,
            'year' => $year,
            'is_practical' => $is_practical
        ];
    }
}

// 1. Total Credits (D or higher => points >= 1.0)
$total_d_credits = 0;
// 2. Yearly Credits
$yearly_credits = [];
// 3. First 2 Years Combined (D or higher)
$first_two_years_d_credits = 0;
// 4. C or Higher Credits (points >= 2.0)
$total_c_credits = 0;
// 5. Subject Specialization (C or higher)
$subject_c_credits = []; // e.g. ['CHEM' => ['total' => 30, 'has_practical' => true]]
// GPA Calculation arrays
$gpa_points = 0;
$gpa_credits = 0;

foreach ($modules as $m) {
    // For GPA: Assuming points >= 0 counts if it's a valid grade. (Wait, GPA usually includes fails, but we exclude ACLT/CMSK/MGMT in dashboard.js. We'll skip exact GPA calculation here assuming dashboard.js passes it in).

    // D or higher (Grade Point >= 1.0)
    // Note: C- is 1.7, D+ is 1.3, D is 1.0, E is 0
    if ($m['points'] >= 1.0) {
        $total_d_credits += $m['credits'];
        
        if (!isset($yearly_credits[$m['year']])) {
            $yearly_credits[$m['year']] = 0;
        }
        $yearly_credits[$m['year']] += $m['credits'];
        
        if ($m['year'] == 1 || $m['year'] == 2) {
            $first_two_years_d_credits += $m['credits'];
        }
    }
    
    // C or higher (According to standard grading: C is 2.0. Is C- included? The prompt says "grade C or higher", usually means C, C+, B-, B, B+, A-, A, A+ i.e., points >= 2.0)
    // Let's assume grading points >= 2.0 for "C or higher" for now, or specifically check the first character of the grade string.
    $gradeLetter = $m['grade'][0]; // 'A', 'B', 'C', 'D', 'E'
    $gradeIsCorHigher = in_array($gradeLetter, ['A', 'B', 'C']);
    
    if ($gradeIsCorHigher) {
        $total_c_credits += $m['credits'];
        
        if (!isset($subject_c_credits[$m['subject']])) {
            $subject_c_credits[$m['subject']] = ['total' => 0, 'has_practical' => false];
        }
        $subject_c_credits[$m['subject']]['total'] += $m['credits'];
        if ($m['is_practical']) {
            $subject_c_credits[$m['subject']]['has_practical'] = true;
        }
    }
}

echo "Total Valid Modules Found: " . count($modules) . "\n";
echo "Total D+ Credits: $total_d_credits\n";
echo "First 2 Years D+ Credits: $first_two_years_d_credits\n";
echo "Total C+ Credits: $total_c_credits\n";
echo "Yearly breakdown: " . json_encode($yearly_credits) . "\n";
echo "Subject breakdown (C+): " . json_encode($subject_c_credits) . "\n";
?>
