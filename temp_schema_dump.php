<?php
require_once 'connection/connection.php';
try {
    Database::setUpConnection();
    $res = Database::search("SHOW TABLES");
    $tables = [];
    while ($row = $res->fetch_array()) {
        $tables[] = $row[0];
    }
    $schema = [];
    foreach ($tables as $table) {
        $res = Database::search("DESCRIBE $table");
        $cols = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $cols[] = $row;
            }
        }
        $schema[$table] = $cols;
    }
    file_put_contents('schema_output.json', json_encode($schema, JSON_PRETTY_PRINT));
    echo "Success";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
