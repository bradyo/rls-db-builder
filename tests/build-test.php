<?php
require_once(__DIR__ . '/bootstrap.php');

use Application\Build\Builder;

$config = require_once(__DIR__ . '/config.php');
$builder = new Builder($config);
$builder->build();

