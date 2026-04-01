<?php
require_once '../connection/connection.php';
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Get search term if provided
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    
    // ensure connection for escaping
    Database::setUpConnection();
    
    // Build query
    $query = "SELECT module_code, module_name, credit_value, is_gpa_module, module_status FROM module";
    
    if (!empty($search)) {
        $search = mysqli_real_escape_string(Database::$connection, $search);
        $query .= " WHERE module_code LIKE '%$search%' OR module_name LIKE '%$search%'";
    }
    
    $query .= " ORDER BY module_code ASC";
    
    // Execute query
    $result = Database::search($query);
    
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        // Convert is_gpa from tinyint to boolean
        $row['is_gpa_module'] = (bool)$row['is_gpa_module'];
        $courses[] = $row;
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'data' => $courses,
        'count' => count($courses)
    ]);

} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>