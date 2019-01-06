<?php

$packages = json_decode(`composer show --format=json`, true);

$packages = array_filter($packages['installed'], function ($package) {
    return 0 === strpos($package['name'], 'phpactor');
});
$packages = array_map(function ($package) {
    return $package['name'];
}, $packages);

foreach ($packages as $package) {
    echo $package . PHP_EOL;
}
