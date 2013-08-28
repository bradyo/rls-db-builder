<?php
require_once(__DIR__ . '/bootstrap.php');

use Application\Build\Builder;

// give the build script enough resources
ini_set('memory_limit', '-1');
ini_set('auto_detect_line_endings', true);

$config = require_once(__DIR__ . '/configs/config.php');
$builder = new Builder($config);
$builder->build();
