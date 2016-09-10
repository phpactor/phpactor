<?php

use Symfony\Component\Console\Application;
use Phactor\Console\ScanCommand;

require_once(__DIR__ . '/../vendor/autoload.php');

$application = new Application();
$application->add(new ScanCommand());
$application->run();
