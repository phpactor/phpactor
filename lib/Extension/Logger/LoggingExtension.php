<?php

namespace Phpactor\Extension\Logger;

use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Phpactor\Extension\Logger\Formatter\FormatterRegistry;
use Phpactor\Extension\Logger\Formatter\PrettyFormatter;
use Psr\Log\LogLevel;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FingersCrossedHandler;
use Phpactor\Container\Container;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;
use Phpactor\Container\ContainerBuilder;
use Psr\Log\LoggerInterface;
use RuntimeException;

class LoggingExtension implements Extension
{
    public const PARAM_ENABLED = 'logging.enabled';
    public const PARAM_FINGERS_CROSSED = 'logging.fingers_crossed';
    public const PARAM_FORMATTER = 'logging.formatter';
    public const PARAM_LEVEL = 'logging.level';
    public const PARAM_NAME = 'logger.name';
    public const PARAM_PATH = 'logging.path';
    public const SERVICE_LOGGER = 'logging.logger';
    public const TAG_FORMATTER = 'logging.formatter';
    private const SERVICE_FORMATTER_REGISTRY = 'logging.formatter_registry';

    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_ENABLED => false,
            self::PARAM_FINGERS_CROSSED => false,
            self::PARAM_PATH => 'application.log',
            self::PARAM_LEVEL => LogLevel::WARNING,
            self::PARAM_NAME => 'logger',
            self::PARAM_FORMATTER => null,
        ]);

        $schema->setTypes([
            self::PARAM_ENABLED => 'boolean',
            self::PARAM_FINGERS_CROSSED => 'boolean',
            self::PARAM_PATH => 'string',
            self::PARAM_LEVEL => 'string',
            self::PARAM_NAME => 'string',
        ]);

        $schema->setEnums([
            self::PARAM_LEVEL => [
                LogLevel::EMERGENCY,
                LogLevel::ALERT,
                LogLevel::CRITICAL,
                LogLevel::ERROR,
                LogLevel::WARNING,
                LogLevel::NOTICE,
                LogLevel::INFO,
                LogLevel::DEBUG,
            ],
        ]);
    }

    public function load(ContainerBuilder $container): void
    {
        $this->registerInfrastructure($container);
        $this->registerFormatters($container);
    }

    public static function channelLogger(Container $container, string $name): LoggerInterface
    {
        return (new LoggerFactory($container->get(self::SERVICE_LOGGER)))->get($name);
    }

    private function registerInfrastructure(ContainerBuilder $container): void
    {
        $container->register(self::SERVICE_LOGGER, function (Container $container) {
            $logger = new Logger('phpactor');

            if (false === $container->parameter(self::PARAM_ENABLED)->bool()) {
                $logger->pushHandler(new NullHandler());
                return $logger;
            }

            $handler = new StreamHandler(
                $container->parameter(self::PARAM_PATH)->string(),
                $container->parameter(self::PARAM_LEVEL)->string(),
            );

            if ($formatter = $container->parameter(self::PARAM_FORMATTER)->stringOrNull()) {
                $handler->setFormatter(
                    $container->expect(
                        self::SERVICE_FORMATTER_REGISTRY,
                        FormatterRegistry::class
                    )->get($formatter)
                );
            }

            if ($container->parameter(self::PARAM_FINGERS_CROSSED)->bool()) {
                $handler = new FingersCrossedHandler($handler);
            }

            $logger->pushHandler($handler);


            return $logger;
        });

        $container->register(self::SERVICE_FORMATTER_REGISTRY, function (Container $container) {
            $serviceMap = [];
            foreach ($container->getServiceIdsForTag(self::TAG_FORMATTER) as $serviceId => $attrs) {
                if (!isset($attrs['alias'])) {
                    throw new RuntimeException(sprintf(
                        'Logging service "%s" must provide an `alias` attribute',
                        $serviceId
                    ));
                }
                $serviceMap[$attrs['alias']] = $serviceId;
            }

            return new FormatterRegistry($container, $serviceMap);
        });
    }

    private function registerFormatters(ContainerBuilder $container): void
    {
        $container->register(PrettyFormatter::class, function () {
            return new PrettyFormatter();
        }, [
            LoggingExtension::TAG_FORMATTER => [
                'alias' => 'pretty',
            ],
        ]);
        $container->register(LineFormatter::class, function () {
            return new LineFormatter();
        }, [
            LoggingExtension::TAG_FORMATTER => [
                'alias' => 'line',
            ],
        ]);
        $container->register(JsonFormatter::class, function () {
            return new JsonFormatter();
        }, [
            LoggingExtension::TAG_FORMATTER => [
                'alias' => 'json',
            ],
        ]);
    }
}
