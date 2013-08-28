<?php
return array(
    'input_path' => __DIR__ . '/test-input',

    'reports_path' => __DIR__ . '/../data/test/reports',
    'archive_path' => __DIR__ . '/../data/test/archive',
    'logs_path' => __DIR__ . '/../data/test/logs',

    'r_exec_path' => '/usr/bin/R',

    'database' => array(
        'host' => 'localhost',
        'name' => 'kaeberle_rls_test',
        'user' => 'root',
        'password' => 'vagrant'
    )
);
