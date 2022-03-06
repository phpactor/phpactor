<?php

namespace Phpactor\Extension\Logger\Tests\Unit\Formatter;

use Monolog\Formatter\FormatterInterface;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\Logger\Formatter\FormatterRegistry;
use Psr\Container\ContainerInterface;
use RuntimeException;

class FormatterRegistryTest extends TestCase
{
    public function testThrowsExceptionIfFormatterNotFound(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not find formatter');
        $container = $this->prophesize(ContainerInterface::class);
        $registry = new FormatterRegistry($container->reveal(), [
            'foo' => 'bar'
        ]);

        $registry->get('zed');
    }

    public function testReturnsFormatter(): void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $formatter = $this->prophesize(FormatterInterface::class);
        $registry = new FormatterRegistry($container->reveal(), [
            'foo' => 'bar'
        ]);

        $container->get('bar')->willReturn($formatter->reveal());

        $this->assertSame($formatter->reveal(), $registry->get('foo'));
    }
}
