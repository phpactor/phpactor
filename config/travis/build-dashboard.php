<?php

$composer = json_decode(file_get_contents(__DIR__ . '/../../composer.json'), true);

$packages = array_filter($composer['require'], function ($key) {
    return 0 === strpos($key, 'phpactor');
}, ARRAY_FILTER_USE_KEY);

ksort($packages);

$output = [];
$output[] = 'Travis Dashboard';
$output[] = '================';
$output[] = '';
$output[] = 'Dependencies';
$output[] = '------------';

$output[] = '| Package | Version | Badge |';
$output[] = '| ------- | ------- | ----- |';
render_package_table($output, $packages);

$extensions = array_filter(explode(PHP_EOL, file_get_contents(__DIR__ . '/extensions-to-test')));

$output[] = '';
$output[] = 'Extensions';
$output[] = '----------';
$output[] = '| Package |  | Badge |';
$output[] = '| ------- | ------- | ----- |';
render_package_table($output, $extensions);

echo implode(PHP_EOL, $output);

function render_package_table(&$output, $packages)
{
    foreach ($packages as $package => $version) {
        if (is_numeric($package)) {
            $package = $version;
        }
        $output[] = sprintf(
            '| %s | %s | %s',
            sprintf('<a href="https://github.com/%s">%s</a>', $package, $package),
            $version === $package ? '' : $version,
            sprintf(
                '[![master](https://travis-ci.org/%s.svg?branch=master)](https://travis-ci.org/%s)',
                $package,
                $package
            )
        );
    }
}
