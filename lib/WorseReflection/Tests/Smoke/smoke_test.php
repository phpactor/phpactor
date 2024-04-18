#!/usr/bin/env php
<?php

use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Bridge\Composer\ComposerSourceLocator;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StubSourceLocator;
use Phpactor\WorseReflection\ReflectorBuilder;
use Symfony\Component\Filesystem\Path;

$autoload = require __DIR__ . '/../../vendor/autoload.php';
$path = __DIR__ . '/../..';
$slowThreshold = 0.25;
$logFile = 'smoke_test.log';
$logHandle = fopen($logFile, 'w');
$opts = array_merge([
    'pattern' => '.*\.php$',
    'offset' => 0,
    'limit' => null
], getopt('', [
    'pattern:',
    'offset:',
    'limit:',
]));

if (isset($argv[1])) {
    $pattern = $argv[1];
}

$reflector = ReflectorBuilder::create()
    ->enableCache()
    ->addLocator(new ComposerSourceLocator($autoload))
    ->addLocator(new StubSourceLocator(
        ReflectorBuilder::create()->build(),
        __DIR__ . '/../../vendor/jetbrains/phpstorm-stubs',
        __DIR__ . '/../Workspace/smoke-cache'
    ))
    ->build();

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$files  = new RegexIterator($files, '{.*' . $opts['pattern'] . '.*}');
$exceptions = [];
$count = 0;

echo 'Legend: N = Not found, E = Error' . "\n" . "\n";

/** @var SplFileInfo $file */
foreach ($files as $file) {
    if ($count < $opts['offset']) {
        $count++;
        continue;
    }

    if (null !== $opts['limit'] && $count > $opts['limit']) {
        break;
    }

    echo $count++ . ' ' . Path::makeRelative($file->getPathname(), getcwd()) . "\n";
    $message = $file->getPathname();
    try {
        $source = TextDocumentBuilder::create(file_get_contents($file->getPathname()))->uri($file->getPathname())->build();
        $classes = $reflector->reflectClassLikesIn($source);

        /** @var ReflectionClass $class */
        foreach ($classes as $class) {
            /** @var ReflectionMethod $method */
            foreach ($class->methods() as $method) {
                $time = microtime(true);
                $method->frame();

                $time = microtime(true) - $time;

                if ($time > $slowThreshold) {
                    fwrite($logHandle, sprintf('%s#%s (%ss)', $class->name()->full(), $method->name(), number_format($time, 2)) . "\n");
                    echo 'S';
                }
            }
        }
    } catch (NotFound $e) {
        fwrite($logHandle, sprintf('%s %s %s: ', 'NOT FOUND', Path::makeRelative($file->getPathname(), getcwd()), $e->getMessage()). "\n");
        echo 'N';
    } catch (Exception $e) {
        echo 'E';
        fwrite($logHandle, sprintf('%s %s [%s] %s', 'ERROR', $message, get_class($e), $e->getMessage())."\n");
        ;
        $exceptions[] = $e;
    } finally {
    }
}

fclose($logHandle);
