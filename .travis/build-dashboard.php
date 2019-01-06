<?php

$packages = json_decode(`composer show --format=json`, true);

$packages = array_filter($packages['installed'], function ($package) {
    return 0 === strpos($package['name'], 'phpactor');
});

$output = [];
$output[] = 'Travis Dashboard';
$output[] = '================';
$output[] = '';
$output[] = '| Package | Travis | Packagist |';
$output[] = '| ------- | ------ | --------- |';

foreach ($packages as $package) {
    $output[] = sprintf(
        '| %s | %s | %s',
        sprintf(
            '<a href="https://github.com/%s">%s</a>',
            $package['name'],
            $package['name']
        ),
        sprintf(
            '[![master](https://travis-ci.org/%s.svg?branch=master)](https://travis-ci.org/%s)',
            $package['name'],
            $package['name']
        ),
        sprintf(
            '[![Latest stable](https://img.shields.io/packagist/v/%s.svg)](https://packagist.org/packages/%s)',
            $package['name'],
            $package['name']
        )
    );
}

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
