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
use Phpactor\Application\Helper\ClassFileNormalizer;
use Phpactor\ClassFileConverter\Adapter\Composer\ComposerClassToFile;
use Phpactor\ClassFileConverter\Adapter\Composer\ComposerFileToClass;
use Phpactor\ClassFileConverter\Domain\ChainClassToFile;
use Phpactor\ClassFileConverter\Domain\ChainFileToClass;
use Phpactor\ClassFileConverter\Domain\ClassToFileFileToClass;
use Phpactor\ClassMover\ClassMover;
use Phpactor\Filesystem\Adapter\Composer\ComposerFileListProvider;
use Phpactor\Filesystem\Adapter\Git\GitFilesystem;
use Phpactor\Filesystem\Adapter\Simple\SimpleFilesystem;
use Phpactor\Filesystem\Domain\ChainFileListProvider;
use Phpactor\Filesystem\Domain\Cwd;
use Phpactor\Filesystem\Domain\FilePath;
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
use Phpactor\Console\Command\ReferencesMethodCommand;
use Phpactor\Application\ClassMethodReferences;
use Phpactor\Filesystem\Domain\FilesystemRegistry;
use Phpactor\Core\GotoDefinition\GotoDefinition;

class CoreExtension implements ExtensionInterface
{
    const APP_NAME = 'phpactor';
    const APP_VERSION = '0.2.0';

    public static $autoloader;

    public function getDefaultConfig()
    {
        $cwd = $this->getBaseCwd();
        return [
            'autoload' => sprintf('%s/vendor/autoload.php', $cwd),
            'cwd' => $cwd,
            'console_dumper_default' => 'indented',
            'cache_dir' => __DIR__ . '/../../cache',
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
            return new Logger('phpactor');
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
            return new ReferencesMethodCommand(
                $container->get('application.method_references'),
                $container->get('console.dumper_registry')
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

            return new DumperRegistry($dumpers, $container->getParameter('console_dumper_default'));
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
            $autoloaderPaths = (array) $container->getParameter('autoload');
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

        $container->register('class_mover.method_finder', function (Container $container) {
            return new \Phpactor\ClassMover\Adapter\WorseTolerant\WorseTolerantMethodFinder($container->get('reflection.reflector'));
        });

        $container->register('class_mover.method_replacer', function (Container $container) {
            return new \Phpactor\ClassMover\Adapter\WorseTolerant\WorseTolerantMethodReplacer();
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
                $container->get('class_to_file.converter')
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
            return new ClassMethodReferences(
                $container->get('application.helper.class_file_normalizer'),
                $container->get('class_mover.method_finder'),
                $container->get('class_mover.method_replacer'),
                $container->get('source_code_filesystem.registry'),
                $container->get('reflection.reflector')
            );
        });


        $container->register('application.helper.class_file_normalizer', function (Container $container) {
            return new ClassFileNormalizer($container->get('class_to_file.converter'));
        });
    }

    /**
     * TODO: Move this to Phpactor\Phpactor.
     */
    private function getBaseCwd($path = null)
    {
        if (is_null($path)) {
            $path = getcwd();
        }

        // Return base CWD where .git directory is present
        if (!file_exists(sprintf('%s/.git', $path)) && $path !== '/') {
            return $this->getBaseCwd(dirname($path));
        }

        return $path;
    }
}
