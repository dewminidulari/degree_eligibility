<?php
require_once '../Connection/connection.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    $module_code = trim($_POST['module_code'] ?? '');
    $module_name = trim($_POST['module_name'] ?? '');
    $credit_value = intval($_POST['credit_value'] ?? 0);
    $is_gpa_module = intval($_POST['is_gpa_module'] ?? 0);
    $module_status = trim($_POST['module_status'] ?? '');
    
    if (empty($module_code) || empty($module_name) || empty($credit_value) || empty($module_status)) {
        throw new Exception('All fields are required');
    }
    
    // Check if module code exists
    $check_query = "SELECT module_code FROM module WHERE module_code = ?";
    $check_stmt = Database::$connection->prepare($check_query);
    $check_stmt->bind_param("s", $module_code);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        throw new Exception('Module code already exists');
    }
    
    // Insert new course
    $insert_query = "INSERT INTO module (module_code, module_name, credit_value, is_gpa_module, module_status) 
                     VALUES (?, ?, ?, ?, ?)";
    $insert_stmt = Database::$connection->prepare($insert_query);
    $insert_stmt->bind_param("ssiis", $module_code, $module_name, $credit_value, $is_gpa_module, $module_status);
    
    if ($insert_stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to insert course');
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>