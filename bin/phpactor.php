<?php
use Phpactor\Console\Application;
use Symfony\Component\Debug\Debug;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

foreach ([
    __DIR__ . '/../../../autoload.php',
    __DIR__ . '/../vendor/autoload.php'
] as $file) {
    if (file_exists($file)) {
        $autoloadFile = $file;
        break;
    }
}

if (!isset($autoloadFile)) {
    echo sprintf(
        'Phpactor dependencies not installed. Run `composer install` (https://getcomposer.org) in "%s"' . PHP_EOL,
        realpath(__DIR__ . '/..')
    );
    exit(255);
}

require_once $autoloadFile;

$minVersion = '7.0.0';

if (version_compare(PHP_VERSION, $minVersion) < 0) {
    echo 'Phpactor requires at least PHP ' . $minVersion;
    exit(255);
}

Debug::enable();

$application = new Application();
$output = new ConsoleOutput();

try {
    $application->initialize();
    $application->run(null, $output);
} catch (Exception $e) {
    $application->renderException($e, $output);
    exit(255);
}
