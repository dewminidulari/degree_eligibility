<?php
require 'Connection/connection.php';
Database::setUpConnection();

// Test the GPA calculation by checking a specific student
$student_no = 'PS/2022/047';

echo "=== Testing GPA Calculation for Student: $student_no ===\n\n";

// Get the student and their modules with grades
$query = "
  SELECT 
    s.student_no,
    s.name_with_initial,
    m.module_code,
    m.module_name,
    m.credit_value,
    gr.grade_code,
    gr.grade_point,
    res.exam_status,
    res.module_enrollment_module_enrollment_id
  FROM student s
  LEFT JOIN module_enrollment me ON me.student_student_no = s.student_no
  LEFT JOIN results res ON res.module_enrollment_module_enrollment_id = me.module_enrollment_id
  LEFT JOIN module m ON m.module_code = res.module_module_code
  LEFT JOIN grade gr ON gr.grade_id = res.grade_grade_id
  WHERE s.student_no = '$student_no'
  ORDER BY m.module_code
";

$result = Database::search($query);
$modules = [];
$completed_modules = [];

echo "Enrollments and Grades:\n";
echo str_pad('Module Code', 15) . str_pad('Module Name', 25) . str_pad('Credits', 10) . str_pad('Grade', 8) . str_pad('Grade Point', 15) . "Status\n";
echo str_repeat("-", 90) . "\n";

while ($row = $result->fetch_assoc()) {
    if ($row['module_code']) {
        echo str_pad($row['module_code'], 15) 
             . str_pad($row['module_name'], 25) 
             . str_pad($row['credit_value'], 10) 
             . str_pad($row['grade_code'] ?? 'N/A', 8) 
             . str_pad($row['grade_point'] ?? '0', 15) 
             . ($row['exam_status'] ?? 'N/A') . "\n";
        
        if ($row['exam_status'] === 'Completed') {
            $modules[] = $row;
            $completed_modules[] = $row;
        }
    }
}

// Calculate GPA manually
$total_points = 0;
$total_credits = 0;

echo "\n=== GPA Calculation (Completed Modules Only) ===\n";
echo str_pad('Module Code', 15) . str_pad('Grade Point', 15) . str_pad('Credit Value', 15) . str_pad('Product', 15) . "\n";
echo str_repeat("-", 60) . "\n";

foreach ($completed_modules as $mod) {
    $product = floatval($mod['grade_point']) * floatval($mod['credit_value']);
    echo str_pad($mod['module_code'], 15) 
         . str_pad($mod['grade_point'], 15) 
         . str_pad($mod['credit_value'], 15) 
         . str_pad(number_format($product, 2), 15) . "\n";
    
    $total_points += $product;
    $total_credits += floatval($mod['credit_value']);
}

$calculated_gpa = $total_credits > 0 ? $total_points / $total_credits : 0;

echo str_repeat("-", 60) . "\n";
echo "Total Products: " . number_format($total_points, 2) . "\n";
echo "Total Credits: " . $total_credits . "\n";
echo "Calculated GPA: " . number_format($calculated_gpa, 2) . "\n\n";

// Now test the get_students.php query
echo "=== Data from get_students.php API ===\n";

$api_query = "SELECT s.student_no,
                      s.name_with_initial,
                      s.full_name,
                      s.admission_year,
                      p.program_id AS program_id,
                      p.program_name,
                      e.year_of_study,
                      ed.ideligibility_for_degree,
                      ed.final_gpa,
                      ed.class_award_class_award_id,
                      ed.completion_year,
                      COALESCE(SUM(m.credit_value), 0) AS total_credits,
                      ROUND(
                          COALESCE(
                              SUM(CASE 
                                  WHEN res.exam_status = 'Completed' THEN gr.grade_point * m.credit_value
                                  ELSE 0
                              END) / 
                              NULLIF(
                                  SUM(CASE 
                                      WHEN res.exam_status = 'Completed' THEN m.credit_value
                                      ELSE 0
                                  END), 0
                              ),
                              0
                          ),
                          2
                      ) AS gpa_value
               FROM student s
               LEFT JOIN program p
                      ON s.program_program_id = p.program_id
               LEFT JOIN enrollment e
                      ON e.student_student_no = s.student_no
               LEFT JOIN module_enrollment me
                      ON me.student_student_no = s.student_no
               LEFT JOIN module m
                      ON m.module_code = me.module_module_code
               LEFT JOIN results res
                      ON res.module_enrollment_module_enrollment_id = me.module_enrollment_id
               LEFT JOIN grade gr
                      ON gr.grade_id = res.grade_grade_id
               LEFT JOIN eligibility_degree ed
                      ON ed.student_student_no = s.student_no
               WHERE s.student_no = '$student_no'
               GROUP BY s.student_no, s.name_with_initial, s.full_name, s.admission_year,
                        p.program_id, p.program_name, e.year_of_study,
                        ed.ideligibility_for_degree, ed.final_gpa, ed.class_award_class_award_id,
                        ed.completion_year
               ORDER BY s.student_no";

$api_result = Database::search($api_query);
$api_data = $api_result->fetch_assoc();

if ($api_data) {
    echo "Student No: " . $api_data['student_no'] . "\n";
    echo "Name: " . $api_data['name_with_initial'] . " (" . $api_data['full_name'] . ")\n";
    echo "Program: " . $api_data['program_name'] . "\n";
    echo "Year of Study: " . $api_data['year_of_study'] . "\n";
    echo "Total Credits: " . $api_data['total_credits'] . "\n";
    echo "Calculated GPA (from API): " . $api_data['gpa_value'] . "\n";
} else {
    echo "Student not found in API query\n";
}
?>
