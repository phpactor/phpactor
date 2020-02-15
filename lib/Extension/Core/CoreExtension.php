<?php

namespace Phpactor\Extension\Core;

use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\Core\Application\Helper\ClassFileNormalizer;
use Phpactor\Extension\Core\Rpc\CacheClearHandler;
use Phpactor\Extension\Core\Rpc\ConfigHandler;
use Phpactor\Extension\Core\Rpc\StatusHandler;
use Phpactor\Extension\Php\Model\PhpVersionResolver;
use Phpactor\Extension\Rpc\RpcExtension;
use Phpactor\FilePathResolverExtension\FilePathResolverExtension;
use Phpactor\Extension\Core\Console\Dumper\DumperRegistry;
use Phpactor\Extension\Core\Console\Dumper\IndentedDumper;
use Phpactor\Extension\Core\Console\Dumper\JsonDumper;
use Phpactor\Extension\Core\Console\Dumper\TableDumper;
use Phpactor\Extension\Core\Console\Prompt\BashPrompt;
use Phpactor\Extension\Core\Console\Prompt\ChainPrompt;
use Phpactor\Extension\Core\Command\ConfigDumpCommand;
use Phpactor\Extension\Core\Application\CacheClear;
use Phpactor\Extension\Core\Command\CacheClearCommand;
use Phpactor\Extension\Core\Application\Status;
use Phpactor\Extension\Core\Command\StatusCommand;
use Phpactor\Container\Container;
use Phpactor\Container\Extension;
use Phpactor\FilePathResolver\Expander\ValueExpander;
use Phpactor\MapResolver\Resolver;
use Phpactor\Container\ContainerBuilder;

class CoreExtension implements Extension
{
    const APP_NAME = 'phpactor';
    const APP_VERSION = '0.2.0';

    const PARAM_DUMPER = 'console_dumper_default';
    const PARAM_XDEBUG_DISABLE = 'xdebug_disable';
    const PARAM_COMMAND = 'command';

    public function configure(Resolver $schema)
    {
        $schema->setDefaults([
            self::PARAM_DUMPER => 'indented',
            self::PARAM_XDEBUG_DISABLE => true,
            self::PARAM_COMMAND => null,
        ]);
    }

    public function load(ContainerBuilder $container)
    {
        $this->registerConsole($container);
        $this->registerApplicationServices($container);
        $this->registerRpc($container);
        $this->registerFilePathExpanders($container);
    }

    private function registerConsole(ContainerBuilder $container)
    {
        $container->register('command.config_dump', function (Container $container) {
            return new ConfigDumpCommand(
                $container->getParameters(),
                $container->get('console.dumper_registry'),
                $container->get('config_loader.candidates'),
                $container->get(FilePathResolverExtension::SERVICE_EXPANDERS)
            );
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => 'config:dump']]);


        $container->register('command.cache_clear', function (Container $container) {
            return new CacheClearCommand(
                $container->get('application.cache_clear')
            );
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => 'cache:clear' ]]);

        $container->register('command.status', function (Container $container) {
            return new StatusCommand(
                $container->get('application.status')
            );
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => 'status' ]]);


        $container->register('console.dumper_registry', function (Container $container) {
            $dumpers = [];
            foreach ($container->getServiceIdsForTag('console.dumper') as $dumperId => $attrs) {
                $dumpers[$attrs['name']] = $container->get($dumperId);
            }

            return new DumperRegistry($dumpers, $container->getParameter(self::PARAM_DUMPER));
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
                $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER)->resolve('%cache%')
            );
        });

        $container->register('application.helper.class_file_normalizer', function (Container $container) {
            return new ClassFileNormalizer($container->get('class_to_file.converter'));
        });

        $container->register('application.status', function (Container $container) {
            return new Status(
                $container->get('source_code_filesystem.registry'),
                $container->get('config_loader.candidates'),
                $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER)->resolve('%project_root%'),
                $container->get(PhpVersionResolver::class)
            );
        });
    }

    private function registerRpc(ContainerBuilder $container)
    {
        $container->register('core.rpc.handler.cache_clear', function (Container $container) {
            return new CacheClearHandler($container->get('application.cache_clear'));
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => CacheClearHandler::NAME] ]);

        $container->register('core.rpc.handler.status', function (Container $container) {
            return new StatusHandler($container->get('application.status'), $container->get('config_loader.candidates'));
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => StatusHandler::NAME] ]);

        $container->register('core.rpc.handler.config', function (Container $container) {
            return new ConfigHandler($container->getParameters());
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => ConfigHandler::CONFIG] ]);
    }

    private function registerFilePathExpanders(ContainerBuilder $container)
    {
        $container->register('core.file_path_resolver.project_config_expander', function (Container $container) {
            $path = $container->getParameter(FilePathResolverExtension::PARAM_PROJECT_ROOT) . '/.phpactor';
            return new ValueExpander('project_config', $path);
        }, [ FilePathResolverExtension::TAG_EXPANDER => [] ]);
    }
}
