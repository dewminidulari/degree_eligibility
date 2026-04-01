<?php
require 'Connection/connection.php';
Database::setUpConnection();
$tables = ['student','enrollment','gpa','eligibility_degree','program'];
foreach ($tables as $t) {
    echo "\nColumns of $t:\n";
    $res = Database::$connection->query("SHOW COLUMNS FROM `$t`");
    while ($row = $res->fetch_assoc()) {
        echo $row['Field'] . "\n";
    }
}
