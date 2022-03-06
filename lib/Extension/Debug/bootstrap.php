<?php

use Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider;
use Symfony\Component\VarDumper\Dumper\ContextProvider\CliContextProvider;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\ServerDumper;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\VarDumper;

$cloner = new VarCloner();
$dumper = new ServerDumper('tcp://127.0.0.1:9912', new CliDumper(), [
    'cli' => new CliContextProvider(),
    'source' => new SourceContextProvider(),
]);

VarDumper::setHandler(function ($var) use ($cloner, $dumper): void {
    $dumper->dump($cloner->cloneVar($var));
});
