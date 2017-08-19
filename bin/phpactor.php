<?php

use Symfony\Component\Console\Input\ArgvInput;

require_once(__DIR__ . '/../vendor/autoload.php');

$minVersion = '7.0.0';

if (version_compare(PHP_VERSION, $minVersion) < 0) {
    echo 'Phpactor requires at least PHP ' . $minVersion;
    exit(255);
}

use Phpactor\UserInterface\Console\Application;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Debug\Debug;

Debug::enable();

$application = new Application();
$output = new ConsoleOutput();

try { 
    $application->initialize();
    $application->run(null, $output);
} catch (\Exception $e) {
    $application->renderException($e, $output);
    exit(255);
}
