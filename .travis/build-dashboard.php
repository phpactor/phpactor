<?php

$composer = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);

$packages = array_filter($composer['require'], function ($key) {
    return 0 === strpos($key, 'phpactor');
}, ARRAY_FILTER_USE_KEY);

ksort($packages);

$output = [];
$output[] = 'Travis Dashboard';
$output[] = '================';
$output[] = '';
$output[] = '| Package | Version | Badge |';
$output[] = '| ------- | ------- | ----- |';

foreach ($packages as $package => $version) {
    $output[] = sprintf(
        '| %s | %s | %s',
        sprintf('<a href="https://github.com/%s">%s</a>', $package, $package),
        $version,
        sprintf(
            '[![master](https://travis-ci.org/%s.svg?branch=master)](https://travis-ci.org/%s)',
            $package,
            $package
        )
    );
}

echo implode(PHP_EOL, $output);
