<?php
require 'Connection/connection.php';
Database::setUpConnection();
$res = Database::search('SHOW TABLES');
while($r = $res->fetch_assoc()) {
    echo $r['Tables_in_degree_eligibility_db'] . PHP_EOL;
}
?>
