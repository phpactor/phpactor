<?php

namespace Phpactor\Extension\Debug\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Debug\DebugExtension;

class DebugExtensionTest extends TestCase
{
    public function testExtension(): void
    {
        $container = PhpactorContainer::fromExtensions([DebugExtension::class]);
        foreach ($container->getServiceIds() as $serviceId) {
            $container->get($serviceId);
        }
        $this->addToAssertionCount(1);
    }
}
