<?php
require_once(__DIR__ . '/src/UniversalClassLoader.php');

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Application' => __DIR__ . '/src'
));
$loader->register();
