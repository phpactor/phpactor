<?php

namespace Phpactor\Extension;

use Composer\Autoload\ClassLoader;
use BetterReflection\SourceLocator\Type\ComposerSourceLocator;
use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\DependencyInjection\Container;
use Symfony\Component\Console\Application;
use Phpactor\Console\Command\ExplainCommand;
use Phpactor\Reflection\ComposerReflector;
use Doctrine\DBAL\Connection;
use Phpactor\Storage\Storage;
use Phpactor\Storage\ConnectionWrapper;
use Doctrine\DBAL\DriverManager;
use Phpactor\Console\Command\CompleteCommand;
use Phpactor\Complete\Completer;
use Phpactor\Complete\Provider\VariableProvider;
use BetterReflection\Reflector\ClassReflector;
use Phpactor\Complete\Provider\FetchProvider;
use Phpactor\Complete\ScopeFactory;
use BetterReflection\SourceLocator\Type\StringSourceLocator;
use BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use BetterReflection\SourceLocator\Type\PhpInternalSourceLocator;

class CoreExtension implements ExtensionInterface
{
    const APP_NAME = 'phpactor';
    const APP_VERSION = '0.1.0';

    public function getDefaultConfig()
    {
        return [
            'db.path' => getcwd() . '/phpactor.sqlite',
            'autoload' => 'vendor/autoload.php',
            'source' => null,
        ];
    }

    public function load(Container $container)
    {
        $this->registerComplete($container);
        $this->registerConsole($container);
        $this->registerStorage($container);
        $this->registerMisc($container);
    }

    private function registerComplete(Container $container)
    {
        $container->register('completer.scope_factory', function ($container) {
            return new ScopeFactory();
        });

        $container->register('completer.provider.variables', function ($container) {
            return new VariableProvider($container->get('reflector'));
        });
        $container->register('completer.provider.property_fetch', function ($container) {
            return new FetchProvider($container->get('reflector'));
        });
        $container->register('completer', function (Container $container) {
            return new Completer(
                $container->get('completer.scope_factory'), [
                $container->get('completer.provider.variables'),
                $container->get('completer.provider.property_fetch')
            ]);
        });
    }

    private function registerStorage(Container $container)
    {
        $container->register('dbal.connection_wrapper', function (Container $container) {
            return new ConnectionWrapper($container->get('dbal.connection'));
        });

        $container->register('dbal.connection', function (Container $container) {
            return DriverManager::getConnection([
                'driver' => 'pdo_sqlite',
                'path' => $container->getParameter('db.path')
            ]);
        });
        $container->register('storage', function (Container $container) {
            return new Storage(
                $container->get('dbal.connection_wrapper')->getConnection()
            );
        });
    }

    private function registerMisc(Container $container)
    {
        $container->register('reflector', function (Container $container) {
            $bootstrap = $container->getParameter('autoload');

            if (!file_exists($bootstrap)) {
                throw new \InvalidArgumentException(sprintf(
                    'Could not locate bootstrap file "%s"', $bootstrap
                ));
            }

            $autoloader = require $bootstrap;

            if (!$autoloader instanceof ClassLoader) {
                throw new \RuntimeException('Autoloader is not an instance of ClassLoader');
            }

            $locators = [];

            // HACK: for testing purposes ...
            if ($source = $container->getParameter('source')) {
                $locators[] = new StringSourceLocator($source);
            }

            $locators[] = new ComposerSourceLocator($autoloader);
            $locators[] = new PhpInternalSourceLocator($autoloader);

            return new ClassReflector(new AggregateSourceLocator($locators));
        });
    }

    private function registerConsole(Container $container)
    {
        $container->register('application', function (Container $container) {
            $application = new Application(self::APP_NAME, self::APP_VERSION);
            $application->addCommands([
                $container->get('command.explain'),
                $container->get('command.complete'),
            ]);

            return $application;
        });

        $container->register('command.complete', function (Container $container) {
            return new CompleteCommand($container->get('completer'));
        });

        $container->register('command.explain', function (Container $container) {
            return new ExplainCommand($container->get('reflector'));
        });
    }
}
