<?php

namespace Phpactor\Extension\Console\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Phpactor\Container\Container;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\Console\Tests\Unit\Example\InvalidExtension;
use Phpactor\Extension\Console\Tests\Unit\Example\TestExtension;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class ConsoleExtensionTest extends TestCase
{
    public function testCreatesCommandLoader(): void
    {
        $container = $this->createContainer();

        $loader = $container->get(ConsoleExtension::SERVICE_COMMAND_LOADER);
        $command = $loader->get('test');

        $this->assertInstanceOf(Command::class, $command);
    }

    public function testCreatesInputAndOutput(): void
    {
        $input = $this->createContainer()->get(ConsoleExtension::SERVICE_INPUT);
        $output = $this->createContainer()->get(ConsoleExtension::SERVICE_OUTPUT);

        $this->assertInstanceOf(ArgvInput::class, $input);
        $this->assertInstanceOf(ConsoleOutput::class, $output);
    }

    public function testThrowsExceptionIfNoNameAttributeProvided(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must have the "name" attribute');
        $container = PhpactorContainer::fromExtensions([
            ConsoleExtension::class,
            InvalidExtension::class
        ]);

        $loader = $container->get(ConsoleExtension::SERVICE_COMMAND_LOADER);
    }

    private function createContainer(): Container
    {
        $container = PhpactorContainer::fromExtensions([
            ConsoleExtension::class,
            TestExtension::class
        ]);

        return $container;
    }
}
