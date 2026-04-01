<?php
require_once '../Connection/connection.php';
header('Content-Type: application/json');

try {
    Database::setUpConnection();

    // Prefer the newer admission_year table, but keep backward compatibility.
    $query = "";
    $result = null;

    $hasAdmissionYear = Database::search("SHOW TABLES LIKE 'admission_year'");
    if ($hasAdmissionYear && $hasAdmissionYear->num_rows > 0) {
        $query = "SELECT admission_year_id AS year_id, year_label FROM admission_year ORDER BY admission_year_id DESC";
        $result = Database::search($query);
    } else {
        $hasAcademicYear = Database::search("SHOW TABLES LIKE 'acedemic_year'");
        if ($hasAcademicYear && $hasAcademicYear->num_rows > 0) {
            $query = "SELECT acedemic_year_id AS year_id, year_label FROM acedemic_year ORDER BY acedemic_year_id DESC";
            $result = Database::search($query);
        }
    }

    if (!$query) {
        throw new Exception('No academic year table found. Expected admission_year or acedemic_year.');
    }

    if (!$result) {
        throw new Exception('Query failed: ' . Database::$connection->error);
    }

    $years = [];
    while ($row = $result->fetch_assoc()) {
        $years[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $years]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
