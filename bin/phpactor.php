<?php

use Symfony\Component\Console\Application;
use Phactor\Console\Command\ScanCommand;
use Phpactor\Extension\CoreExtension;
use PhpBench\DependencyInjection\Container;
use Symfony\Component\Debug\Debug;

require_once(__DIR__ . '/../vendor/autoload.php');

Debug::enable();

$container = new Container([
    CoreExtension::class,
], []);
$container->init();
$container->get('application')->run();
