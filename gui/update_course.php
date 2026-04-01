<?php
require_once '../connection/connection.php';
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    Database::setUpConnection();

    $normalizeStatusCode = function($status) {
        $value = strtoupper(trim((string)$status));
        if ($value === 'COMPULSORY') return 'C';
        if ($value === 'OPTIONAL' || $value === 'ELECTIVE') return 'O';
        if ($value === 'AUXILIARY' || $value === 'AUX') return 'A';
        if ($value === 'COMPULSORY/OPTIONAL' || $value === 'C/O') return 'C/O';
        if ($value === 'C' || $value === 'O' || $value === 'A') return $value;
        return '';
    };

    $original_code = isset($_POST['original_module_code']) ? $_POST['original_module_code'] : '';
    $module_code = isset($_POST['module_code']) ? $_POST['module_code'] : '';
    $module_name = isset($_POST['module_name']) ? $_POST['module_name'] : '';
    $credit_value = isset($_POST['credit_value']) ? $_POST['credit_value'] : '';
    $is_gpa_module = isset($_POST['is_gpa_module']) ? $_POST['is_gpa_module'] : '';
    $module_status = isset($_POST['module_status']) ? $normalizeStatusCode($_POST['module_status']) : '';

    if (empty($original_code) || empty($module_code) || empty($module_name) || empty($credit_value) || $is_gpa_module === '' || empty($module_status)) {
        throw new Exception('All fields are required. Module status must be C, O, C/O, or A.');
    }

    // Escape input
    $orig = mysqli_real_escape_string(Database::$connection, $original_code);
    $code = mysqli_real_escape_string(Database::$connection, $module_code);
    $name = mysqli_real_escape_string(Database::$connection, $module_name);
    $credit = (int)$credit_value;
    $gpa = (int)$is_gpa_module;
    $status = mysqli_real_escape_string(Database::$connection, $module_status);

    // If changing the module code, first verify new code doesn't exist
    if ($orig !== $code) {
        $check = Database::search("SELECT * FROM module WHERE module_code = '$code'");
        if ($check->num_rows > 0) {
            throw new Exception("A course with code $code already exists.");
        }
    }

    $query = "UPDATE module SET 
                module_code = '$code',
                module_name = '$name',
                credit_value = $credit,
                is_gpa_module = $gpa,
                module_status = '$status'
              WHERE module_code = '$orig'";

    Database::iud($query);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
