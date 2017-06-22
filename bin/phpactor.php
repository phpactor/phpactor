<?php

use Phpactor\UserInterface\Console\Application;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Debug\Debug;

require_once(__DIR__ . '/../vendor/autoload.php');

Debug::enable();

$application = new Application();
$output = new ConsoleOutput();

try { 
    $application->initialize();
    $application->run(null, $output);
} catch (\Exception $e) {
    $application->renderException($e, $output);
}
