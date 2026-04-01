<?php
$_FILES['csv_file'] = [
    'name' => 'student_upload_template.csv',
    'type' => 'text/csv',
    'tmp_name' => __DIR__ . '/student_upload_template.csv',
    'error' => 0,
    'size' => filesize(__DIR__ . '/student_upload_template.csv')
];
require 'gui/upload_students_csv.php';
?>
