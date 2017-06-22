<?php

namespace Phpactor\Extension;

use Composer\Autoload\ClassLoader;
use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use Symfony\Component\Console\Application;
use DTL\ClassFileConverter\Composer\ComposerClassToFile;
use DTL\ClassFileConverter\Composer\ComposerFileToClass;
use DTL\ClassFileConverter\CompositeTransformer;
use Phpactor\UserInterface\Console\Command\MoveCommand;
use Phpactor\Application\ClassMover as ClassMoverApp;
use DTL\Filesystem\Adapter\Git\GitFilesystem;
use DTL\Filesystem\Domain\Cwd;
use DTL\ClassMover\ClassMover;

class CoreExtension implements ExtensionInterface
{
    const APP_NAME = 'phpactor';
    const APP_VERSION = '0.2.0';

    public function getDefaultConfig()
    {
        return [
            'autoload' => 'vendor/autoload.php',
            'cwd' => getcwd(),
        ];
    }

    public function load(Container $container)
    {
        $this->registerConsole($container);
        $this->registerComposer($container);
        $this->registerClassToFile($container);
        $this->registerClassMover($container);
        $this->registerSourceCodeFilesystem($container);
        $this->registerApplicationServices($container);
    }

    private function registerConsole(Container $container)
    {
        $container->register('application', function (Container $container) {
            $application = new Application(self::APP_NAME, self::APP_VERSION);
            $application->addCommands([
                $container->get('command.move'),
            ]);

            return $application;
        });

        $container->register('command.move', function (Container $container) {
            return new MoveCommand(
                $container->get('application.class_mover')
            );
        });
    }

    private function registerComposer(Container $container)
    {
        $container->register('composer.class_name_resolver', function (Container $container) {
            return new ClassNameResolver($container->get('composer.class_loader'));
        });

        $container->register('composer.class_loader', function (Container $container) {
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

            return $autoloader;
        });
    }

    private function registerClassToFile(Container $container)
    {
        $container->register('class_to_file.converter', function (Container $container) {
            return new CompositeTransformer(
                $container->get('class_to_file.class_to_file'),
                $container->get('class_to_file.file_to_class')
            );
        });

        $container->register('class_to_file.class_to_file', function (Container $container) {
            return new ComposerClassToFile($container->get('composer.class_loader'));
        });

        $container->register('class_to_file.file_to_class', function (Container $container) {
            return new ComposerFileToClass($container->get('composer.class_loader'));
        });
    }

    private function registerClassMover(Container $container)
    {
        $container->register('class_mover.class_mover', function (Container $container) {
            return new ClassMover();
        });
    }

    private function registerSourceCodeFilesystem(Container $container)
    {
        $container->register('source_code_filesystem.git', function (Container $container) {
            return new GitFilesystem(Cwd::fromCwd($container->getParameter('cwd')));
        });
    }

    private function registerApplicationServices(Container $container)
    {
        $container->register('application.class_mover', function (Container $container) {
            return new ClassMoverApp(
                $container->get('class_to_file.converter'),
                $container->get('class_mover.class_mover'),
                $container->get('source_code_filesystem.git')
            );
        });
    }
}
