<?php
require 'Connection/connection.php';
Database::setUpConnection();
$res = Database::search('DESCRIBE path_way;');
while($row = $res->fetch_assoc()) echo json_encode($row)."\n";
?>
