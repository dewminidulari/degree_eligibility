<?php
$output = shell_exec('php get_students.php');
$data = json_decode($output, true);
if (isset($data['success']) && $data['success']) {
    $counts = [];
    foreach ($data['data'] as $student) {
        $no = $student['student_no'];
        if (!isset($counts[$no])) {
            $counts[$no] = 0;
        }
        $counts[$no]++;
    }
    foreach ($counts as $no => $count) {
        if ($count > 1) {
            echo "Duplicate Student in JSON Output: $no (Count: $count)\n";
        }
    }
    echo "Done checking for duplicates.\n";
} else {
    echo "Failed to get generic output or decode it.\n";
}
?>
