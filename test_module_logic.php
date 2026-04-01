<?php
require 'Connection/connection.php';
Database::setUpConnection();

$query = "
SELECT 
    module_code, 
    credit_value,
    SUBSTRING(module_code, 1, 4) as subject_code,
    SUBSTRING(module_code, 5, 1) as year_of_study,
    SUBSTRING(module_code, 6, 1) as semester,
    RIGHT(TRIM(module_code), 1) as is_practical
FROM module
LIMIT 10;
";

$result = Database::search($query);

echo str_pad('Module_Code', 15) . str_pad('Credits', 10) . str_pad('Subj', 8) . str_pad('Year', 6) . str_pad('Sem', 6) . str_pad('Prac?', 8) . "\n";
echo str_repeat("-", 55) . "\n";

while($row = $result->fetch_assoc()) {
    $prac = ($row['is_practical'] == '1') ? 'Yes' : 'No';
    echo str_pad($row['module_code'], 15);
    echo str_pad($row['credit_value'], 10);
    echo str_pad($row['subject_code'], 8);
    echo str_pad($row['year_of_study'], 6);
    echo str_pad($row['semester'], 6);
    echo str_pad($prac, 8);
    echo "\n";
}
?>
