<?php

namespace Phpactor\Extension\Logger\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\MapResolver\Resolver;

class LoggingExtensionTest extends TestCase
{
    public function testLoggingDisabled(): void
    {
        $container = $this->create([
            LoggingExtension::PARAM_ENABLED => false,
        ]);
        $logger = $container->get('logging.logger');
        assert($logger instanceof Logger);
        $handlers = $logger->getHandlers();
        $this->assertCount(1, $handlers);
        $this->assertInstanceOf(NullHandler::class, $handlers[0]);
    }

    #[DataProvider('provideLoggingFormatters')]
    public function testLoggingFormatters(string $formatter): void
    {
        $container = $this->create([
            LoggingExtension::PARAM_ENABLED => true,
        ]);
        $logger = $container->get('logging.logger');
        assert($logger instanceof Logger);
        $handlers = $logger->getHandlers();
        $this->assertCount(1, $handlers);
        $this->assertInstanceOf(StreamHandler::class, $handlers[0]);
    }

    public static function provideLoggingFormatters()
    {
        yield [
            'line'
        ];
        yield [
            'json'
        ];
        yield [
            'pretty'
        ];
    }

    public function testFingersCrossed(): void
    {
        $container = $this->create([
            LoggingExtension::PARAM_ENABLED => true,
            LoggingExtension::PARAM_FINGERS_CROSSED => true,
        ]);
        $logger = $container->get('logging.logger');
        assert($logger instanceof Logger);
        $handlers = $logger->getHandlers();
        $this->assertCount(1, $handlers);
        $this->assertInstanceOf(FingersCrossedHandler::class, $handlers[0]);
    }

    public function testCustomFormatter(): void
    {
        $fname = tempnam(sys_get_temp_dir(), 'phpactor_test');
        $container = $this->create([
            LoggingExtension::PARAM_FORMATTER => 'json',
            LoggingExtension::PARAM_ENABLED => true,
            LoggingExtension::PARAM_PATH => $fname,
            LoggingExtension::PARAM_LEVEL => 'debug',
        ]);
        $logger = $container->get('logging.logger');
        assert($logger instanceof Logger);
        $logger->info('asd');
        $result = json_decode(file_get_contents($fname));
        $this->assertNotNull($result, 'Decoded JSON');
        unlink($fname);
    }

    private function create(array $options): Container
    {
        $container = PhpactorContainer::fromExtensions([
            LoggingExtension::class,
            ExampleExtension::class,
        ], $options);

        return $container;
    }
}

class ExampleExtension implements Extension
{
    public function load(ContainerBuilder $container): void
    {
        $container->register('json_formatter', function (Container $container) {
            return new JsonFormatter();
        }, [ LoggingExtension::TAG_FORMATTER => ['alias'=> 'json2']]);
    }


    public function configure(Resolver $schema): void
    {
    }
}
