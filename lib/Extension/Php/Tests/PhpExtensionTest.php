<?php

namespace Phpactor\Extension\Php\Tests;

use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\Php\Model\PhpVersionResolver;
use Phpactor\Extension\Php\PhpExtension;

class PhpExtensionTest extends IntegrationTestCase
{
    public function testExtension(): void
    {
        $container = PhpactorContainer::fromExtensions([
            PhpExtension::class,
            LoggingExtension::class,
            FilePathResolverExtension::class,
        ]);
        $version = $container->get(PhpVersionResolver::class)->resolve();
        self::assertNotNull($version);
    }
}
