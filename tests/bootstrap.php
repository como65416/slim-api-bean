<?php

require_once __DIR__ . "/../vendor/autoload.php";

$kernel = AspectMock\Kernel::getInstance();
$kernel->init([
    'debug' => true,
    'cacheDir' => __DIR__ . "/../aspect_mock_cache/",
    'includePaths' => [
        __DIR__ . '/../src'
    ]
]);