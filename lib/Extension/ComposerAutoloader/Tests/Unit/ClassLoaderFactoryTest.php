<?php

namespace Phpactor\Extension\ComposerAutoloader\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\ComposerAutoloader\ClassLoaderFactory;
use Psr\Log\NullLogger;

class ClassLoaderFactoryTest extends TestCase
{
    public function testClassLoader(): void
    {
        $logger = new NullLogger();
        $loader = (new ClassLoaderFactory(__DIR__ . '/../../../../../vendor/composer', $logger))->getLoader();
        $file = $loader->findFile(__CLASS__);
        self::assertEquals(__FILE__, $file);
    }
}
