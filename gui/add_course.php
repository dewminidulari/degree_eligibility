<?php
require_once '../Connection/connection.php';
// include composer autoloader if available (used for Excel parsing)
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // initialise connection early
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

    // bulk upload handler
    if (isset($_FILES['course_file']) && $_FILES['course_file']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['course_file']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['course_file']['name'], PATHINFO_EXTENSION));
        $imported = 0;

        if (in_array($ext, ['xls','xlsx'])) {
            // try PhpSpreadsheet if available
            if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
                throw new Exception('Spreadsheet support not available; install PhpSpreadsheet or upload a CSV file instead.');
            }
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmpName);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
        } elseif ($ext === 'csv') {
            $rows = [];
            if (($handle = fopen($tmpName, 'r')) !== false) {
                // detect delimiter by looking at first line
                $first = fgets($handle);
                rewind($handle);
                $delimiter = ',';
                if ($first !== false) {
                    // choose semicolon if it appears more often than comma
                    if (substr_count($first, ';') > substr_count($first, ',')) {
                        $delimiter = ';';
                    }
                }
                while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
                    $rows[] = $data;
                }
                fclose($handle);
            }
        } else {
            throw new Exception('Unsupported file type');
        }

        $skipped = 0;
        $details = [];
        $rowNo = 1; // header counted as row 1
        foreach (array_slice($rows, 1) as $row) {
            $rowNo++;
            // remove BOM on first column and trim
            $firstCol = $row[0] ?? '';
            $firstCol = preg_replace('/^\xEF\xBB\xBF/', '', $firstCol);
            $firstCol = trim($firstCol);

            if ($firstCol === '') {
                $skipped++;
                $details[] = "row $rowNo skipped: empty module code";
                continue;
            }
            $module_code = $firstCol;
            $module_name = trim($row[1] ?? '');
            $credit_value = intval($row[2] ?? 0);
            $is_gpa_module = intval($row[3] ?? 0);
            $module_status = $normalizeStatusCode($row[4] ?? '');

            if ($module_name === '' || $credit_value === 0 || $module_status === '') {
                $skipped++;
                $details[] = "row $rowNo skipped: missing/invalid required field (module_status must be C, O, C/O, or A)";
                continue;
            }

            // duplicate check (exists in database)
            $check_query = "SELECT module_code FROM module WHERE module_code = '" .
                            addslashes($module_code) . "'";
            $check_result = Database::search($check_query);
            if ($check_result->num_rows > 0) {
                $skipped++;
                $details[] = "row $rowNo skipped: duplicate module code $module_code";
                continue;
            }

            $insert_query = "INSERT INTO module (module_code, module_name, credit_value, is_gpa_module, module_status) ";
            $insert_query .= "VALUES ('" . addslashes($module_code) . "', '" . addslashes($module_name) . "', " .
                              $credit_value . ", " . $is_gpa_module . ", '" . addslashes($module_status) . "')";
            Database::iud($insert_query);
            $imported++;
        }

        echo json_encode([
            'success' => true,
            'imported' => $imported,
            'skipped' => $skipped,
            'details' => $details
        ]);
        exit;
    }

    // manual insert path below
    $module_code = trim($_POST['module_code'] ?? '');
    $module_name = trim($_POST['module_name'] ?? '');
    $credit_value = intval($_POST['credit_value'] ?? 0);
    $is_gpa_module = intval($_POST['is_gpa_module'] ?? 0);
    $module_status = $normalizeStatusCode($_POST['module_status'] ?? '');
    
    if (empty($module_code) || empty($module_name) || empty($credit_value) || empty($module_status)) {
        throw new Exception('All fields are required. Module status must be C, O, C/O, or A.');
    }
    
    // Check if module code exists
    $check_query = "SELECT module_code FROM module WHERE module_code = '" . addslashes($module_code) . "'";
    $check_result = Database::search($check_query);
    
    if ($check_result->num_rows > 0) {
        throw new Exception('Module code already exists');
    }
    
    // Insert new course using your iud method
    $insert_query = "INSERT INTO module (module_code, module_name, credit_value, is_gpa_module, module_status) ";
    $insert_query .= "VALUES ('" . addslashes($module_code) . "', '" . addslashes($module_name) . "', " .
                     $credit_value . ", $is_gpa_module, '" . addslashes($module_status) . "')";
    
    Database::iud($insert_query);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>