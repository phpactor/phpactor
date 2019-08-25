<?php

$composer = json_decode(file_get_contents(__DIR__ . '/../../composer.json'), true);

$packages = array_filter($composer['require'], function ($key) {
    return 0 === strpos($key, 'phpactor');
}, ARRAY_FILTER_USE_KEY);

foreach ($packages as $package => $version) {
    echo $package . PHP_EOL;
}
