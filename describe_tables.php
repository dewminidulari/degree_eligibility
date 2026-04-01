<?php
require 'Connection/connection.php';
Database::setUpConnection();
$schema = ['student' => [], 'program' => []];

$res = Database::search('DESCRIBE student;');
while($row = $res->fetch_assoc()) $schema['student'][] = $row;

$res = Database::search('DESCRIBE program;');
while($row = $res->fetch_assoc()) $schema['program'][] = $row;

file_put_contents('schema_dump.json', json_encode($schema, JSON_PRETTY_PRINT));
echo "Dumped to schema_dump.json\n";
?>
