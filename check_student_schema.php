<?php
require_once 'connection/connection.php';

Database::setUpConnection();

// Check student table columns
echo "=== STUDENT TABLE ===\n";
$res = Database::search("SHOW COLUMNS FROM student");
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . " | " . $row['Type'] . " | Null: " . $row['Null'] . " | Key: " . $row['Key'] . " | Default: " . ($row['Default'] ?? 'NULL') . "\n";
}

echo "\n=== MODULE_ENROLLMENT TABLE ===\n";
$res = Database::search("SHOW COLUMNS FROM module_enrollment");
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . " | " . $row['Type'] . " | Null: " . $row['Null'] . " | Key: " . $row['Key'] . " | Default: " . ($row['Default'] ?? 'NULL') . "\n";
}

echo "\n=== RESULTS TABLE ===\n";
$res = Database::search("SHOW COLUMNS FROM results");
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . " | " . $row['Type'] . " | Null: " . $row['Null'] . " | Key: " . $row['Key'] . " | Default: " . ($row['Default'] ?? 'NULL') . "\n";
}

// Check if admission_year table exists
echo "\n=== CHECKING FOR ADMISSION_YEAR TABLE ===\n";
$res = Database::search("SHOW TABLES LIKE 'admission_year'");
if ($res && $res->num_rows > 0) {
    echo "admission_year table EXISTS\n";
    $res2 = Database::search("SHOW COLUMNS FROM admission_year");
    while ($row = $res2->fetch_assoc()) {
        echo $row['Field'] . " | " . $row['Type'] . "\n";
    }
} else {
    echo "admission_year table DOES NOT EXIST\n";
}
?>
