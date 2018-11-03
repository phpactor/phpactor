<?php

namespace Phpactor\Extension\Core;

use Composer\Autoload\ClassLoader;
use Monolog\Handler\NullHandler;
use Phpactor\Exension\Logger\LoggingExtension;
use Phpactor\Extension\Core\Application\Helper\ClassFileNormalizer;
use Phpactor\FilePathResolverExtension\FilePathResolverExtension;
use Phpactor\FilePathResolver\Expander\ValueExpander;
use Phpactor\Filesystem\Domain\Cwd;
use Phpactor\Extension\Core\Console\Dumper\DumperRegistry;
use Phpactor\Extension\Core\Console\Dumper\IndentedDumper;
use Phpactor\Extension\Core\Console\Dumper\JsonDumper;
use Phpactor\Extension\Core\Console\Dumper\TableDumper;
use Phpactor\Extension\Core\Console\Prompt\BashPrompt;
use Phpactor\Extension\Core\Console\Prompt\ChainPrompt;
use Phpactor\Extension\Core\Command\ConfigDumpCommand;
use Monolog\Logger;
use Psr\Log\LogLevel;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FingersCrossedHandler;
use Phpactor\Extension\Core\Application\CacheClear;
use Phpactor\Extension\Core\Command\CacheClearCommand;
use Phpactor\Extension\Core\Application\Status;
use Phpactor\Extension\Core\Command\StatusCommand;
use Phpactor\Container\Container;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;
use Phpactor\Container\ContainerBuilder;

class CoreExtension implements Extension
{
    const APP_NAME = 'phpactor';
    const APP_VERSION = '0.2.0';
    const WORKING_DIRECTORY = 'cwd';
    const DUMPER = 'console_dumper_default';
    const CACHE_DIR = 'cache_dir';
    const XDEBUG_DISABLE = 'xdebug_disable';
    const VENDOR_DIRECTORY = 'vendor_dir';
    const COMMAND = 'command';

    public function configure(Resolver $schema)
    {
        $schema->setDefaults([
            self::WORKING_DIRECTORY => getcwd(),
            self::DUMPER => 'indented',
            self::CACHE_DIR => null,
            self::XDEBUG_DISABLE => true,
            self::VENDOR_DIRECTORY => null,
            self::COMMAND => null,
        ]);
    }

    public function load(ContainerBuilder $container)
    {
        $container->register('core.phpactor_vendor', function (Container $container) {
            return new ValueExpander('%phpactor_vendor%', $container->getParameter(self::VENDOR_DIRECTORY));
        }, [ FilePathResolverExtension::TAG_EXPANDER => [] ]);
        $this->registerConsole($container);
        $this->registerApplicationServices($container);
    }

    private function registerConsole(ContainerBuilder $container)
    {
        $container->register('command.config_dump', function (Container $container) {
            return new ConfigDumpCommand(
                $container->getParameters(),
                $container->get('console.dumper_registry'),
                $container->get('config.paths')
            );
        }, [ 'ui.console.command' => [ 'name' => 'config:dump']]);


        $container->register('command.cache_clear', function (Container $container) {
            return new CacheClearCommand(
                $container->get('application.cache_clear')
            );
        }, [ 'ui.console.command' => [ 'name' => 'cache:clear' ]]);

        $container->register('command.status', function (Container $container) {
            return new StatusCommand(
                $container->get('application.status')
            );
        }, [ 'ui.console.command' => [ 'name' => 'status' ]]);


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

        $container->register('console.prompter', function (Container $container) {
            return new ChainPrompt([
                new BashPrompt()
            ]);
        });
    }

    private function registerApplicationServices(Container $container)
    {
        $container->register('application.cache_clear', function (Container $container) {
            return new CacheClear(
                $container->getParameter(self::CACHE_DIR)
            );
        });

        $container->register('application.helper.class_file_normalizer', function (Container $container) {
            return new ClassFileNormalizer($container->get('class_to_file.converter'));
        });

        $container->register('application.status', function (Container $container) {
            return new Status(
                $container->get('source_code_filesystem.registry'),
                $container->get('config.paths'),
                $container->getParameter(self::WORKING_DIRECTORY)
            );
        });
    }
}
