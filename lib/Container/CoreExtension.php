<?php

namespace Phpactor\Container;

use Composer\Autoload\ClassLoader;
use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use Symfony\Component\Console\Application;
use Phpactor\UserInterface\Console\Command\ClassMoveCommand;
use Phpactor\Application\ClassMover as ClassMoverApp;
use DTL\Filesystem\Adapter\Git\GitFilesystem;
use DTL\Filesystem\Domain\Cwd;
use DTL\ClassMover\ClassMover;
use DTL\Filesystem\Adapter\Simple\SimpleFilesystem;
use Phpactor\Application\FileInfo;
use DTL\TypeInference\TypeInference;
use Phpactor\UserInterface\Console\Command\FileInfoAtOffsetCommand;
use Phpactor\Application\ClassSearch;
use Phpactor\UserInterface\Console\Command\ClassSearchCommand;
use DTL\Filesystem\Adapter\Composer\ComposerFilesystem;
use DTL\Filesystem\Domain\FilePath;
use Phpactor\UserInterface\Console\Command\FileInfoCommand;
use DTL\ClassFileConverter\Domain\ClassToFileFileToClass;
use DTL\ClassFileConverter\Adapter\Composer\ComposerClassToFile;
use DTL\ClassFileConverter\Adapter\Composer\ComposerFileToClass;
use DTL\ClassFileConverter\Domain\ChainFileToClass;
use DTL\ClassFileConverter\Domain\ChainClassToFile;
use DTL\Filesystem\Adapter\Composer\ComposerFileListProvider;
use DTL\Filesystem\Domain\ChainFileListProvider;
use Phpactor\UserInterface\Console\Prompt\ChainPrompt;
use Phpactor\UserInterface\Console\Prompt\BashPrompt;
use DTL\TypeInference\Adapter\ClassToFile\ClassToFileSourceCodeLoader;
use DTL\TypeInference\Adapter\TolerantParser\TolerantTypeInferer;
use DTL\TypeInference\Adapter\WorseReflection\WorseSourceCodeLocator;
use DTL\TypeInference\Adapter\WorseReflection\WorseMemberTypeResolver;
use DTL\WorseReflection\Reflector;

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
                $container->get('application.class_mover'),
                $container->get('console.prompter')
            );
        });
        $container->register('command.class_search', function (Container $container) {
            return new ClassSearchCommand(
                $container->get('application.class_search')
            );
        });

        $container->register('command.file_offset', function (Container $container) {
            return new FileInfoAtOffsetCommand(
                $container->get('application.file_info')
            );
        });

        $container->register('command.file_info', function (Container $container) {
            return new FileInfoCommand(
                $container->get('application.file_info')
            );
        });

        $container->register('console.prompter', function (Container $container) {
            return new ChainPrompt([
                new BashPrompt()
            ]);
        });
    }

    private function registerComposer(Container $container)
    {
        $container->register('composer.class_loaders', function (Container $container) {
            $autoloaderPaths = (array) $container->getParameter('autoload');
            $autoloaders = [];

            foreach ($autoloaderPaths as $autoloaderPath) {
                if (!file_exists($autoloaderPath)) {
                    throw new \InvalidArgumentException(sprintf(
                        'Could not locate autoloaderPath file "%s"', $autoloaderPath
                    ));
                }

                $autoloader = require $autoloaderPath;

                if (!$autoloader instanceof ClassLoader) {
                    throw new \RuntimeException('Autoloader is not an instance of ClassLoader');
                }

                $autoloaders[] = $autoloader;
            }

            return $autoloaders;
        });
    }

    private function registerClassToFile(Container $container)
    {
        $container->register('class_to_file.converter', function (Container $container) {
            return new ClassToFileFileToClass(
                $container->get('class_to_file.class_to_file'),
                $container->get('class_to_file.file_to_class')
            );
        });

        $container->register('class_to_file.class_to_file', function (Container $container) {
            $classToFiles = [];
            foreach ($container->get('composer.class_loaders') as $classLoader) {
                $classToFiles[] = new ComposerClassToFile($classLoader);
            }

            return new ChainClassToFile($classToFiles);
        });

        $container->register('class_to_file.file_to_class', function (Container $container) {
            $fileToClasses = [];
            foreach ($container->get('composer.class_loaders') as $classLoader) {
                $fileToClasses[] =  new ComposerFileToClass($classLoader);
            }
            return new ChainFileToClass($fileToClasses);
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
            $providers = [];
            $cwd = FilePath::fromString($container->getParameter('cwd'));
            foreach ($container->get('composer.class_loaders') as $classLoader) {
                $providers[] = new ComposerFileListProvider($cwd, $classLoader);
            }
            return new SimpleFilesystem($cwd, new ChainFileListProvider($providers));
        });
    }

    private function registerTypeInference(Container $container)
    {
        $container->register('type_inference.source_code_loader', function (Container $container) {
            return new ClassToFileSourceCodeLoader($container->get('class_to_file.converter'));
        });
        $container->register('type_inference.type_inference', function (Container $container) {
            return new TolerantTypeInferer(null, new WorseMemberTypeResolver(
                    new Reflector(
                        new WorseSourceCodeLocator(
                            $container->get('type_inference.source_code_loader'))
                        )
                    )
            );
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

        $container->register('application.file_info', function (Container $container) {
            return new FileInfo(
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
