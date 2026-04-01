<?php
require_once '../Connection/connection.php';
header('Content-Type: application/json');

try {
    Database::setUpConnection();

    $query = "SELECT program_id, program_name FROM program ORDER BY program_name";
    $result = Database::search($query);

    if (!$result) {
        throw new Exception('Query failed: ' . Database::$connection->error);
    }

    $programs = [];
    while ($row = $result->fetch_assoc()) {
        $programs[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $programs]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
