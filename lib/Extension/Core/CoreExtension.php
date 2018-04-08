<?php

namespace Phpactor\Extension\Core;

use Composer\Autoload\ClassLoader;
use Phpactor\Extension\ClassMover\Application\ClassCopy;
use Phpactor\Extension\ClassMover\Application\ClassMover as ClassMoverApp;
use Phpactor\Extension\WorseReflection\Application\ClassReflector;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilestem\Application\ClassSearch;
use Phpactor\Extension\ClassToFile\Application\FileInfo;
use Phpactor\Extension\WorseReflection\Application\OffsetInfo;
use Phpactor\Extension\Core\Application\Helper\ClassFileNormalizer;
use Phpactor\ClassFileConverter\Adapter\Composer\ComposerClassToFile;
use Phpactor\ClassFileConverter\Adapter\Composer\ComposerFileToClass;
use Phpactor\ClassFileConverter\Domain\ChainClassToFile;
use Phpactor\ClassFileConverter\Domain\ChainFileToClass;
use Phpactor\ClassFileConverter\Domain\ClassToFileFileToClass;
use Phpactor\ClassMover\ClassMover;
use Phpactor\Filesystem\Domain\Cwd;
use Phpactor\Extension\ClassMover\Command\ClassCopyCommand;
use Phpactor\Extension\ClassMover\Command\ClassMoveCommand;
use Phpactor\Extension\WorseReflection\Command\ClassReflectorCommand;
use Phpactor\Extension\SourceCodeFilesystem\Command\ClassSearchCommand;
use Phpactor\Extension\WorseReflection\Command\OffsetInfoCommand;
use Phpactor\Extension\ClassToFile\Command\FileInfoCommand;
use Phpactor\Console\Dumper\DumperRegistry;
use Phpactor\Console\Dumper\IndentedDumper;
use Phpactor\Console\Dumper\JsonDumper;
use Phpactor\Console\Dumper\TableDumper;
use Phpactor\Console\Prompt\BashPrompt;
use Phpactor\Console\Prompt\ChainPrompt;
use Symfony\Component\Console\Application;
use Phpactor\Extension\Core\Command\ConfigDumpCommand;
use Monolog\Logger;
use Psr\Log\LogLevel;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FingersCrossedHandler;
use Phpactor\Extension\Core\Application\CacheClear;
use Phpactor\Extension\Core\Command\CacheClearCommand;
use Phpactor\ClassFileConverter\Adapter\Simple\SimpleFileToClass;
use Phpactor\ClassFileConverter\Adapter\Simple\SimpleClassToFile;
use Phpactor\Extension\Core\Application\Status;
use Phpactor\Extension\Core\Command\StatusCommand;
use Symfony\Component\Debug\Debug;
use Phpactor\Extension\Container;
use Phpactor\Extension\Extension;
use Phpactor\Extension\Schema;
use Phpactor\Extension\ContainerBuilder;

class CoreExtension implements Extension
{
    const APP_NAME = 'phpactor';
    const APP_VERSION = '0.2.0';
    const LOGGING_PATH = 'logging.path';
    const LOGGING_LEVEL = 'logging.level';
    const AUTOLOAD = 'autoload';
    const WORKING_DIRECTORY = 'cwd';
    const DUMPER = 'console_dumper_default';
    const CACHE_DIR = 'cache_dir';
    const LOGGING_ENABLED = 'logging.enabled';
    const LOGGING_FINGERS_CROSSED = 'logging.fingers_crossed';
    const AUTOLOAD_DEREGISTER = 'autoload.deregister';

    public static $autoloader;

    public function getDefaultConfig()
    {
    }

    public function configure(Schema $schema)
    {
        $schema->setDefaults([
            self::AUTOLOAD => 'vendor/autoload.php',
            self::AUTOLOAD_DEREGISTER => true,
            self::WORKING_DIRECTORY => getcwd(),
            self::DUMPER => 'indented',
            self::CACHE_DIR => __DIR__ . '/../../cache',
            self::LOGGING_ENABLED => false,
            self::LOGGING_FINGERS_CROSSED => true,
            self::LOGGING_PATH => 'phpactor.log',
            self::LOGGING_LEVEL => LogLevel::WARNING,
        ]);
    }

    public function load(ContainerBuilder $container)
    {
        $this->registerMonolog($container);
        $this->registerConsole($container);
        $this->registerComposer($container);
        $this->registerClassToFile($container);
        $this->registerApplicationServices($container);
    }

    private function registerMonolog(ContainerBuilder $container)
    {
        $container->register('monolog.logger', function (Container $container) {
            $logger = new Logger('phpactor');

            if (false === $container->getParameter(self::LOGGING_ENABLED)) {
                return $logger;
            }

            $handler = new StreamHandler(
                $container->getParameter(self::LOGGING_PATH),
                $container->getParameter(self::LOGGING_LEVEL)
            );

            if ($container->getParameter(self::LOGGING_FINGERS_CROSSED)) {
                $handler = new FingersCrossedHandler($handler);
            }

            $logger->pushHandler($handler);

            return $logger;
        });
    }

    private function registerConsole(ContainerBuilder $container)
    {
        // ---------------
        // Commands
        // ---------------
        $container->register('command.class_search', function (Container $container) {
            return new ClassSearchCommand(
                $container->get('application.class_search'),
                $container->get('console.dumper_registry')
            );
        }, [ 'ui.console.command' => []]);

        $container->register('command.offset_info', function (Container $container) {
            return new OffsetInfoCommand(
                $container->get('application.offset_info'),
                $container->get('console.dumper_registry')
            );
        }, [ 'ui.console.command' => []]);

        $container->register('command.file_info', function (Container $container) {
            return new FileInfoCommand(
                $container->get('application.file_info'),
                $container->get('console.dumper_registry')
            );
        }, [ 'ui.console.command' => []]);

        $container->register('command.class_reflector', function (Container $container) {
            return new ClassReflectorCommand(
                $container->get('application.class_reflector'),
                $container->get('console.dumper_registry')
            );
        }, [ 'ui.console.command' => []]);

        $container->register('command.config_dump', function (Container $container) {
            return new ConfigDumpCommand(
                $container->getParameters(),
                $container->get('console.dumper_registry'),
                $container->get('config.paths')
            );
        }, [ 'ui.console.command' => []]);


        $container->register('command.cache_clear', function (Container $container) {
            return new CacheClearCommand(
                $container->get('application.cache_clear')
            );
        }, [ 'ui.console.command' => []]);

        $container->register('command.status', function (Container $container) {
            return new StatusCommand(
                $container->get('application.status')
            );
        }, [ 'ui.console.command' => []]);


        // ---------------
        // Dumpers
        // ---------------
        $container->register('console.dumper_registry', function (Container $container) {
            $dumpers = [];
            foreach ($container->getServiceIdsForTag('console.dumper') as $dumperId => $attrs) {
                $dumpers[$attrs['name']] = $container->get($dumperId);
            }

            return new DumperRegistry($dumpers, $container->getParameter(self::DUMPER));
        });

        $container->register('console.dumper.indented', function (Container $container) {
            return new IndentedDumper();
        }, [ 'console.dumper' => ['name' => 'indented']]);

        $container->register('console.dumper.json', function (Container $container) {
            return new JsonDumper();
        }, [ 'console.dumper' => ['name' => 'json']]);

        $container->register('console.dumper.fieldvalue', function (Container $container) {
            return new TableDumper();
        }, [ 'console.dumper' => ['name' => 'fieldvalue']]);


        // ---------------
        // Misc
        // ---------------
        $container->register('console.prompter', function (Container $container) {
            return new ChainPrompt([
                new BashPrompt()
            ]);
        });
    }

    private function registerComposer(ContainerBuilder $container)
    {
        $container->register('composer.class_loaders', function (Container $container) {
            $currentAutoloaders = spl_autoload_functions();

            // prefix relative paths with the configured CWD
            $autoloaderPaths = array_map(function ($path) use ($container) {
                if (substr($path, 0, 1) == '/') {
                    return $path;
                }

                return $container->getParameter(self::WORKING_DIRECTORY) . '/' . $path;
            }, (array) $container->getParameter(self::AUTOLOAD));

            $autoloaders = [];

            foreach ($autoloaderPaths as $autoloaderPath) {
                if (false === file_exists($autoloaderPath)) {
                    $container->get('monolog.logger')->warning(sprintf(
                        'Could not find autoloader "%s"',
                        $autoloaderPath
                    ));
                    continue;
                }

                $autoloader = require $autoloaderPath;

                if (!$autoloader instanceof ClassLoader) {
                    throw new \RuntimeException('Autoloader is not an instance of ClassLoader');
                }

                $autoloaders[] = $autoloader;
            }

            if ($container->getParameter(self::AUTOLOAD_DEREGISTER)) {
                foreach (spl_autoload_functions() as $autoloadFunction) {
                    spl_autoload_unregister($autoloadFunction);
                }

                foreach ($currentAutoloaders as $autoloader) {
                    spl_autoload_register($autoloader);
                }
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

            if (empty($classToFiles)) {
                $classToFiles[] = new SimpleClassToFile($container->getParameter(self::WORKING_DIRECTORY));
            }

            return new ChainClassToFile($classToFiles);
        });

        $container->register('class_to_file.file_to_class', function (Container $container) {
            $fileToClasses = [];
            foreach ($container->get('composer.class_loaders') as $classLoader) {
                $fileToClasses[] =  new ComposerFileToClass($classLoader);
            }

            if (empty($fileToClasses)) {
                $fileToClasses[] = new SimpleFileToClass();
            }

            return new ChainFileToClass($fileToClasses);
        });
    }

    private function registerApplicationServices(Container $container)
    {
        $container->register('application.file_info', function (Container $container) {
            return new FileInfo(
                $container->get('class_to_file.converter'),
                $container->get('source_code_filesystem.simple')
            );
        });

        $container->register('application.offset_info', function (Container $container) {
            return new OffsetInfo(
                $container->get('reflection.reflector'),
                $container->get('application.helper.class_file_normalizer')
            );
        });

        $container->register('application.class_search', function (Container $container) {
            return new ClassSearch(
                $container->get('source_code_filesystem.registry'),
                $container->get('class_to_file.converter'),
                $container->get('reflection.reflector')
            );
        });

        $container->register('application.class_reflector', function (Container $container) {
            return new ClassReflector(
                $container->get('application.helper.class_file_normalizer'),
                $container->get('reflection.reflector')
            );
        });

        $container->register('application.cache_clear', function (Container $container) {
            return new CacheClear(
                $container->getParameter(self::CACHE_DIR)
            );
        });

        $container->register('application.helper.class_file_normalizer', function (Container $container) {
            return new ClassFileNormalizer($container->get('class_to_file.converter'));
        });

        $container->register('application.status', function (Container $container) {
            return new Status($container->get('source_code_filesystem.registry'));
        });
    }
}
