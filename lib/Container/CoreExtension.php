<?php

namespace Phpactor\Container;

use Composer\Autoload\ClassLoader;
use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use Symfony\Component\Console\Application;
use DTL\ClassFileConverter\Composer\ComposerClassToFile;
use DTL\ClassFileConverter\Composer\ComposerFileToClass;
use DTL\ClassFileConverter\CompositeTransformer;
use Phpactor\UserInterface\Console\Command\ClassMoveCommand;
use Phpactor\Application\ClassMover\ClassMover as ClassMoverApp;
use DTL\Filesystem\Adapter\Git\GitFilesystem;
use DTL\Filesystem\Domain\Cwd;
use DTL\ClassMover\ClassMover;
use DTL\Filesystem\Adapter\Simple\SimpleFilesystem;
use Phpactor\Application\InformationForOffset\InformationForOffset;
use DTL\TypeInference\TypeInference;
use Phpactor\UserInterface\Console\Command\InformationForOffsetCommand;
use Phpactor\Application\ClassSearch\ClassSearch;
use Phpactor\UserInterface\Console\Command\ClassSearchCommand;
use DTL\Filesystem\Adapter\Composer\ComposerFilesystem;
use DTL\Filesystem\Domain\FilePath;

class CoreExtension implements ExtensionInterface
{
    const APP_NAME = 'phpactor';
    const APP_VERSION = '0.2.0';

    static $autoloader;

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
        $this->registerTypeInference($container);
        $this->registerSourceCodeFilesystem($container);
        $this->registerApplicationServices($container);
    }

    private function registerConsole(Container $container)
    {
        $container->register('command.class_move', function (Container $container) {
            return new ClassMoveCommand(
                $container->get('application.class_mover')
            );
        });
        $container->register('command.class_search', function (Container $container) {
            return new ClassSearchCommand(
                $container->get('application.class_search')
            );
        });

        $container->register('command.offsetinfo', function (Container $container) {
            return new InformationForOffsetCommand(
                $container->get('application.information_for_offset')
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
            return new GitFilesystem(FilePath::fromString($container->getParameter('cwd')));
        });
        $container->register('source_code_filesystem.simple', function (Container $container) {
            return new SimpleFilesystem(FilePath::fromString($container->getParameter('cwd')));
        });
        $container->register('source_code_filesystem.composer', function (Container $container) {
            return new ComposerFilesystem(FilePath::fromString($container->getParameter('cwd')), $container->get('composer.class_loader'));
        });
    }

    private function registerTypeInference(Container $container)
    {
        $container->register('type_inference.type_inference', function (Container $container) {
            return new TypeInference();
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

        $container->register('application.information_for_offset', function (Container $container) {
            return new InformationForOffset(
                $container->get('type_inference.type_inference'),
                $container->get('class_to_file.converter'),
                $container->get('source_code_filesystem.simple')
            );
        });

        $container->register('application.class_search', function (Container $container) {
            return new ClassSearch(
                $container->get('source_code_filesystem.composer'),
                $container->get('class_to_file.converter')
            );
        });
    }
}
