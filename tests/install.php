<?php
$config = require_once(__DIR__ . '/config.php');

// set up production database
$host = $config['database']['host'];
$database = $config['database']['name'];;
$user = $config['database']['user'];
$password = $config['database']['password'];
$baseCommand = "mysql -h $host -u $user --password='$password' $database";

$sqlFiles = array(
    __DIR__ . '/../configs/database/structure.sql',
    __DIR__ . '/../configs/database/genes-data.sql'
);
foreach ($sqlFiles as $file) {
    $command = $baseCommand . " < $file";
    exec($command);
}
