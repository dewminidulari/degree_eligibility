<?php
require 'Connection/connection.php';
Database::setUpConnection();
$res = Database::$connection->query('SELECT * FROM student LIMIT 20');
while ($row = $res->fetch_assoc()) {
    echo json_encode($row) . "\n";
}
