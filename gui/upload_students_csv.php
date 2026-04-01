<?php
require_once '../connection/connection.php';
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    Database::setUpConnection();

    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Upload unsuccesfull");
    }

    $fileTmpPath = $_FILES['csv_file']['tmp_name'];
    $fileName = $_FILES['csv_file']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($fileExtension, ['csv'])) {
        throw new Exception("Invalid file extension. Please upload a .csv file.");
    }

    $handle = fopen($fileTmpPath, "r");
    if ($handle === FALSE) {
        throw new Exception("Unable to open the uploaded file.");
    }

    // Read headers
    $headers = fgetcsv($handle, 1000, ",");
    if (!$headers) {
        fclose($handle);
        throw new Exception("File is empty or invalid CSV.");
    }

    // Required headers for CSV upload
    $expected = ['Student Number', 'Name with Initials', 'Full Name', 'Degree Program', 'Admission Year', 'Module Code', 'Results', 'Attempt No', 'Exam Status'];
    
    // Aggressively normalize headers for comparison (remove BOM, spaces, special chars)
    $normalize = function($str) {
        $str = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $str); // Strip BOM and non-ASCII
        $str = preg_replace('/[^a-zA-Z0-9]/', '', $str); // Keep only alphanumeric
        return strtolower($str);
    };

    $normalized_headers = array_map($normalize, $headers);
    $normalized_expected = array_map($normalize, $expected);
    
    // Check missing columns based on aggressive lowercase match
    $missing = array_diff($normalized_expected, $normalized_headers);
    
    if (count($missing) > 0) {
        fclose($handle);
        throw new Exception("Invalid CSV headers. Required headers are exactly: " . implode(', ', $expected));
    }

    // Map normalized header names back to their index in the row
    $headerIndex = [];
    foreach ($normalized_headers as $index => $norm_val) {
        $headerIndex[$norm_val] = $index;
    }

    $getCell = function($row, $key) use ($headerIndex) {
        if (!isset($headerIndex[$key])) {
            return '';
        }
        $idx = $headerIndex[$key];
        return isset($row[$idx]) ? trim($row[$idx]) : '';
    };

    $inserted = 0;
    $updated = 0;
    $skipped = 0;
    $errors = [];
    $seenRowSignatures = [];

    // Caches for lookups
    $programsCache = [];
    $res = Database::search("SELECT program_id, program_name FROM program");
    while ($row = $res->fetch_assoc()) {
        // Normalize names for fuzzy matching if needed
        $programsCache[strtolower(trim($row['program_name']))] = $row['program_id'];
    }

    // Some deployments no longer have pathway table/column.
    // Admission Year lookup cache for foreign key
    $admissionYearCache = [];
    $res = Database::search("SELECT admission_year_id, year_label FROM admission_year");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $admissionYearCache[strtolower(trim($row['year_label']))] = $row['admission_year_id'];
        }
    }

    // Grade lookup cache
    $gradeCache = [];
    $res = Database::search("SELECT grade_id, grade_code FROM grade");
    while ($row = $res->fetch_assoc()) {
        $gradeCache[strtolower(trim($row['grade_code']))] = $row['grade_id'];
    }

    // Add headers to errors array so it's a valid CSV on download
    $errorHeaders = $headers;
    $errorHeaders[] = 'Error Message';
    $errors[] = $errorHeaders;

    Database::$connection->begin_transaction();

    $rowNum = 1;
    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $rowNum++;
        if (empty($row)) continue;

        // Extract Data
        $s_no_raw      = $getCell($row, 'studentnumber');
        $initials_raw  = $getCell($row, 'namewithinitials');
        $f_name_raw    = $getCell($row, 'fullname');
        $prog_name     = $getCell($row, 'degreeprogram');
        $adm_year_raw  = $getCell($row, 'admissionyear');
        $mod_code_raw  = $getCell($row, 'modulecode');
        $result        = $getCell($row, 'results');
        $attempt_raw   = $getCell($row, 'attemptno');
        $exam_stat_raw = $getCell($row, 'examstatus');

        $s_no      = Database::$connection->real_escape_string($s_no_raw);
        $f_name    = Database::$connection->real_escape_string($f_name_raw);
        $initials  = Database::$connection->real_escape_string($initials_raw);
        $adm_year  = Database::$connection->real_escape_string($adm_year_raw);
        $mod_code  = Database::$connection->real_escape_string($mod_code_raw);
        $attempt   = Database::$connection->real_escape_string($attempt_raw);
        $exam_stat = Database::$connection->real_escape_string($exam_stat_raw);

        if (empty($s_no_raw) || empty($initials_raw) || empty($f_name_raw) || empty($prog_name) || empty($adm_year_raw) || empty($mod_code_raw) || empty($result) || empty($attempt_raw)) {
            $skipped++;
            $errorRow = $row;
            $errorRow[] = "Required fields cannot be empty. Required: Student Number, Name with Initials, Full Name, Degree Program, Admission Year, Module Code, Results, Attempt No. (Exam Status is optional)";
            $errors[] = $errorRow;
            continue;
        }

        if (preg_match('/\s/', $mod_code_raw)) {
            $skipped++;
            $errorRow = $row;
            $errorRow[] = "Module Code must not contain spaces.";
            $errors[] = $errorRow;
            continue;
        }

        $rowSignature = strtolower(implode('|', [
            trim($s_no_raw),
            trim($initials_raw),
            trim($f_name_raw),
            trim($prog_name),
            trim($adm_year_raw),
            trim($mod_code_raw),
            trim($result),
            trim($attempt_raw),
            trim($exam_stat_raw)
        ]));

        if (isset($seenRowSignatures[$rowSignature])) {
            $skipped++;
            $errorRow = $row;
            $errorRow[] = "Duplicate row in CSV file. Same data appears more than once.";
            $errors[] = $errorRow;
            continue;
        }
        $seenRowSignatures[$rowSignature] = true;

        // Lookups
        $prog_id = isset($programsCache[strtolower($prog_name)]) ? $programsCache[strtolower($prog_name)] : 0;
        
        if ($prog_id === 0 && !empty($prog_name)) {
            // Attempt a loose match if program not explicitly found (e.g. they provided ID instead of name by mistake)
            if (is_numeric($prog_name)) {
                $prog_id = (int)$prog_name;
            }
        }
        
        // Ensure program name is valid before attempting insertion
        if ($prog_id === 0 || $prog_id === null) {
            $skipped++;
            $errorRow = $row;
            $errorRow[] = "Degree Program '$prog_name' does not match any existing program in the database.";
            $errors[] = $errorRow;
            continue;
        }

        // Insert/Update Student
        $query_student = "INSERT INTO student 
            (student_no, full_name, name_with_initial, admission_year, program_program_id) 
            VALUES ('$s_no', '$f_name', '$initials', '$adm_year', $prog_id)
            ON DUPLICATE KEY UPDATE 
            full_name=VALUES(full_name), name_with_initial=VALUES(name_with_initial), admission_year=VALUES(admission_year),
            program_program_id=VALUES(program_program_id)";
        
        Database::iud($query_student);

        // Next, if Module Code is provided, insert enrollment and results
        if (!empty($mod_code)) {
            $grade_id = isset($gradeCache[strtolower($result)]) ? $gradeCache[strtolower($result)] : null;

            if (!$grade_id && !empty($result)) {
                $skipped++;
                $errorRow = $row;
                $errorRow[] = "Result grade '$result' is not recognized in the grade system.";
                $errors[] = $errorRow;
                continue;
            }

            // Verify Module exists
            $modCheck = Database::search("SELECT module_code FROM module WHERE module_code = '$mod_code'");
            if ($modCheck->num_rows == 0) {
                 $skipped++;
                 $errorRow = $row;
                 $errorRow[] = "Module '$mod_code' does not exist in the course database.";
                 $errors[] = $errorRow;
                 continue;
            }

            // Get admission year ID for foreign key
            $admissionYearId = null;
            if (!empty($adm_year_raw)) {
                $admissionYearId = isset($admissionYearCache[strtolower(trim($adm_year_raw))]) ? 
                                   $admissionYearCache[strtolower(trim($adm_year_raw))] : null;
            }

            if (!$admissionYearId) {
                $skipped++;
                $errorRow = $row;
                $errorRow[] = "Admission Year '$adm_year_raw' not found in the system.";
                $errors[] = $errorRow;
                continue;
            }

            // Check if enrollment exists
            $enrCheck = Database::search("SELECT module_enrollment_id FROM module_enrollment 
                                          WHERE student_student_no = '$s_no' 
                                          AND module_module_code = '$mod_code'
                                          AND admission_year_admission_year_id = $admissionYearId");
            
            $enrollment_id = null;
            if ($enrCheck && $enrCheck->num_rows > 0) {
                $enrollment_id = $enrCheck->fetch_assoc()['module_enrollment_id'];
            } else {
                Database::iud("INSERT INTO module_enrollment (admission_year_admission_year_id, student_student_no, module_module_code) 
                               VALUES ($admissionYearId, '$s_no', '$mod_code')");
                $enrollment_id = Database::$connection->insert_id;
            }

            // Upsert Result
            if ($enrollment_id) {
                $resCheck = Database::search("SELECT result_id FROM results WHERE module_enrollment_module_enrollment_id = $enrollment_id");
                if ($resCheck && $resCheck->num_rows > 0) {
                    $existingResult = Database::search("SELECT attempt_no, exam_status, grade_grade_id, module_module_code FROM results WHERE module_enrollment_module_enrollment_id = $enrollment_id LIMIT 1");
                    if ($existingResult && $existingResult->num_rows > 0) {
                        $r = $existingResult->fetch_assoc();
                        $isExactDuplicate = (
                            strtolower(trim((string)$r['attempt_no'])) === strtolower(trim((string)$attempt_raw)) &&
                            strtolower(trim((string)($r['exam_status'] ?? ''))) === strtolower(trim((string)($exam_stat_raw ?? ''))) &&
                            (int)$r['grade_grade_id'] === (int)$grade_id &&
                            strtolower(trim((string)$r['module_module_code'])) === strtolower(trim((string)$mod_code_raw))
                        );

                        if ($isExactDuplicate) {
                            $skipped++;
                            $errorRow = $row;
                            $errorRow[] = "Duplicate data already exists in database for this student/module/attempt/result.";
                            $errors[] = $errorRow;
                            continue;
                        }
                    }

                    // Update - handle empty exam_status as NULL
                    $examStatusSql = empty($exam_stat) ? 'NULL' : "'$exam_stat'";
                    Database::iud("UPDATE results SET 
                                   attempt_no = '$attempt', 
                                   exam_status = $examStatusSql, 
                                   grade_grade_id = $grade_id,
                                   module_module_code = '$mod_code'
                                   WHERE module_enrollment_module_enrollment_id = $enrollment_id");
                    $updated++;
                } else {
                    // Insert - handle empty exam_status as NULL
                    $examStatusSql = empty($exam_stat) ? 'NULL' : "'$exam_stat'";
                    Database::iud("INSERT INTO results (attempt_no, exam_status, grade_grade_id, module_module_code, module_enrollment_module_enrollment_id)
                                   VALUES ('$attempt', $examStatusSql, $grade_id, '$mod_code', $enrollment_id)");
                    $inserted++;
                }
            }
        }
    }

    fclose($handle);
    Database::$connection->commit();

    // Remove the headers row from errors if no actual errors occurred
    if (count($errors) === 1) {
        $errors = [];
    }

    echo json_encode([
        'success' => true, 
        'inserted' => $inserted,
        'updated' => $updated,
        'skipped' => $skipped,
        'errors' => $errors
    ]);

} catch (Exception $e) {
    if (isset(Database::$connection) && Database::$connection && !Database::$connection->connect_errno) {
        Database::$connection->rollback();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
