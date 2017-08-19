<?php

use Symfony\Component\Console\Input\ArgvInput;

$autoloadFile = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadFile)) {
    echo sprintf(
        'Phpactor dependencies not installed. Run `composer install` (https://getcomposer.org) in "%s"' . PHP_EOL,
        realpath(__DIR__ . '/..')
    );
    exit(255);
}

require_once($autoloadFile);

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
