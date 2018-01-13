<?php

namespace Phpactor\Container;

use Composer\Autoload\ClassLoader;
use PhpBench\DependencyInjection\ExtensionInterface;
use Phpactor\Application\ClassCopy;
use Phpactor\Application\ClassMover as ClassMoverApp;
use Phpactor\Application\ClassReflector;
use Phpactor\Application\ClassSearch;
use Phpactor\Application\FileInfo;
use Phpactor\Application\OffsetInfo;
use Phpactor\Application\Navigator;
use Phpactor\Application\Helper\ClassFileNormalizer;
use Phpactor\ClassFileConverter\Adapter\Composer\ComposerClassToFile;
use Phpactor\ClassFileConverter\Adapter\Composer\ComposerFileToClass;
use Phpactor\ClassFileConverter\Domain\ChainClassToFile;
use Phpactor\ClassFileConverter\Domain\ChainFileToClass;
use Phpactor\ClassFileConverter\Domain\ClassToFileFileToClass;
use Phpactor\ClassMover\ClassMover;
use Phpactor\Filesystem\Domain\Cwd;
use Phpactor\Console\Command\ClassCopyCommand;
use Phpactor\Console\Command\ClassMoveCommand;
use Phpactor\Console\Command\ClassReflectorCommand;
use Phpactor\Console\Command\ClassSearchCommand;
use Phpactor\Console\Command\OffsetInfoCommand;
use Phpactor\Console\Command\FileInfoCommand;
use Phpactor\Console\Dumper\DumperRegistry;
use Phpactor\Console\Dumper\IndentedDumper;
use Phpactor\Console\Dumper\JsonDumper;
use Phpactor\Console\Dumper\TableDumper;
use Phpactor\Console\Prompt\BashPrompt;
use Phpactor\Console\Prompt\ChainPrompt;
use Symfony\Component\Console\Application;
use Phpactor\Console\Command\ConfigDumpCommand;
use PhpBench\DependencyInjection\Container;
use Monolog\Logger;
use Phpactor\Application\Complete;
use Phpactor\Console\Command\CompleteCommand;
use Phpactor\Application\ClassReferences;
use Phpactor\Console\Command\ReferencesClassCommand;
use Phpactor\Console\Command\ReferencesMemberCommand;
use Phpactor\Application\ClassMemberReferences;
use Psr\Log\LogLevel;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FingersCrossedHandler;
use Phpactor\ClassFileConverter\PathFinder;
use Phpactor\Application\CacheClear;
use Phpactor\Console\Command\CacheClearCommand;

class CoreExtension implements ExtensionInterface
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
    const NAVIGATOR_AUTOCREATE = 'navigator.autocreate';

    public static $autoloader;

    public function getDefaultConfig()
    {
        return [
            self::AUTOLOAD => 'vendor/autoload.php',
            self::WORKING_DIRECTORY => getcwd(),
            self::DUMPER => 'indented',
            self::CACHE_DIR => __DIR__ . '/../../cache',
            self::LOGGING_ENABLED => false,
            self::LOGGING_FINGERS_CROSSED => true,
            self::LOGGING_PATH => 'phpactor.log',
            self::LOGGING_LEVEL => LogLevel::WARNING,
            self::NAVIGATOR_AUTOCREATE => [],
        ];
    }

    public function load(Container $container)
    {
        $this->registerMonolog($container);
        $this->registerConsole($container);
        $this->registerComposer($container);
        $this->registerClassToFile($container);
        $this->registerClassMover($container);
        $this->registerApplicationServices($container);
    }

    private function registerMonolog(Container $container)
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

    private function registerConsole(Container $container)
    {
        // ---------------
        // Commands
        // ---------------
        $container->register('command.class_move', function (Container $container) {
            return new ClassMoveCommand(
                $container->get('application.class_mover'),
                $container->get('console.prompter')
            );
        }, [ 'ui.console.command' => []]);

        $container->register('command.class_copy', function (Container $container) {
            return new ClassCopyCommand(
                $container->get('application.class_copy'),
                $container->get('console.prompter')
            );
        }, [ 'ui.console.command' => []]);

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
                $container->configLoader()
            );
        }, [ 'ui.console.command' => []]);

        $container->register('command.complete', function (Container $container) {
            return new CompleteCommand(
                $container->get('application.complete'),
                $container->get('console.dumper_registry')
            );
        }, [ 'ui.console.command' => []]);

        $container->register('command.class_references', function (Container $container) {
            return new ReferencesClassCommand(
                $container->get('application.class_references'),
                $container->get('console.dumper_registry')
            );
        }, [ 'ui.console.command' => []]);

        $container->register('command.method_references', function (Container $container) {
            return new ReferencesMemberCommand(
                $container->get('application.method_references'),
                $container->get('console.dumper_registry')
            );
        }, [ 'ui.console.command' => []]);

        $container->register('command.cache_clear', function (Container $container) {
            return new CacheClearCommand(
                $container->get('application.cache_clear')
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

    private function registerComposer(Container $container)
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
                if (!file_exists($autoloaderPath)) {
                    throw new \InvalidArgumentException(sprintf(
                        'Could not locate autoloaderPath file "%s"',
                        $autoloaderPath
                    ));
                }

                $autoloader = require $autoloaderPath;

                if (!$autoloader instanceof ClassLoader) {
                    throw new \RuntimeException('Autoloader is not an instance of ClassLoader');
                }

                $autoloaders[] = $autoloader;
            }

            foreach (spl_autoload_functions() as $autoloadFunction) {
                spl_autoload_unregister($autoloadFunction);
            }

            foreach ($currentAutoloaders as $autoloader) {
                spl_autoload_register($autoloader);
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
            return new ClassMover(
                $container->get('class_mover.class_finder'),
                $container->get('class_mover.ref_replacer')
            );
        });

        $container->register('class_mover.class_finder', function (Container $container) {
            return new \Phpactor\ClassMover\Adapter\TolerantParser\TolerantClassFinder();
        });

        $container->register('class_mover.member_finder', function (Container $container) {
            return new \Phpactor\ClassMover\Adapter\WorseTolerant\WorseTolerantMemberFinder($container->get('reflection.reflector'));
        });

        $container->register('class_mover.member_replacer', function (Container $container) {
            return new \Phpactor\ClassMover\Adapter\WorseTolerant\WorseTolerantMemberReplacer();
        });

        $container->register('class_mover.ref_replacer', function (Container $container) {
            return new \Phpactor\ClassMover\Adapter\TolerantParser\TolerantClassReplacer();
        });
    }

    private function registerApplicationServices(Container $container)
    {
        $container->register('application.class_mover', function (Container $container) {
            return new ClassMoverApp(
                $container->get('application.helper.class_file_normalizer'),
                $container->get('class_mover.class_mover'),
                $container->get('source_code_filesystem.registry')
            );
        });

        $container->register('application.class_copy', function (Container $container) {
            return new ClassCopy(
                $container->get('application.helper.class_file_normalizer'),
                $container->get('class_mover.class_mover'),
                $container->get('source_code_filesystem.git')
            );
        });

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

        $container->register('application.complete', function (Container $container) {
            return new Complete(
                $container->get('reflection.reflector'),
                $container->get('application.helper.class_file_normalizer')
            );
        });

        $container->register('application.class_references', function (Container $container) {
            return new ClassReferences(
                $container->get('application.helper.class_file_normalizer'),
                $container->get('class_mover.class_finder'),
                $container->get('class_mover.ref_replacer'),
                $container->get('source_code_filesystem.registry')
            );
        });

        $container->register('application.method_references', function (Container $container) {
            return new ClassMemberReferences(
                $container->get('application.helper.class_file_normalizer'),
                $container->get('class_mover.member_finder'),
                $container->get('class_mover.member_replacer'),
                $container->get('source_code_filesystem.registry'),
                $container->get('reflection.reflector')
            );
        });

        $container->register('application.navigator', function (Container $container) {
            return new Navigator(
                $container->get('path_finder.path_finder'),
                $container->get('application.class_new'),
                $container->getParameter(self::NAVIGATOR_AUTOCREATE)
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
    }
}
