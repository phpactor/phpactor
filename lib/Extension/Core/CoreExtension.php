<?php

namespace Phpactor\Extension\Core;

use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\Core\Application\Helper\ClassFileNormalizer;
use Phpactor\Extension\Core\Command\DebugContainerCommand;
use Phpactor\Extension\Core\Command\TrustCommand;
use Phpactor\Extension\Core\Rpc\CacheClearHandler;
use Phpactor\Extension\Core\Rpc\ConfigHandler;
use Phpactor\Extension\Core\Rpc\StatusHandler;
use Phpactor\Extension\Core\Rpc\TrustHandler;
use Phpactor\Extension\Core\Trust\Trust;
use Phpactor\Extension\Php\Model\PhpVersionResolver;
use Phpactor\Extension\Rpc\RpcExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
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
use Phpactor\ConfigLoader\Core\PathCandidates;
use Phpactor\FilePathResolver\Expanders;
use Phpactor\FilePathResolver\PathResolver;
use Phpactor\Filesystem\Domain\FilesystemRegistry;
use Phpactor\MapResolver\Resolver;
use Phpactor\Container\ContainerBuilder;

class CoreExtension implements Extension
{
    const APP_NAME = 'phpactor';
    const APP_VERSION = '0.2.0';
    const PARAM_DUMPER = 'console_dumper_default';
    const PARAM_XDEBUG_DISABLE = 'xdebug_disable';
    const PARAM_COMMAND = 'command';
    const PARAM_MIN_MEMORY_LIMIT = 'core.min_memory_limit';
    const PARAM_SCHEMA = '$schema';
    const PARAM_PROJECT_CONFIG_CANDIDATES = 'core.project_config_candidates';
    const PARAM_TRUST = 'core.trust';
    const PARAM_TRUSTED = 'core.trusted';

    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_DUMPER => 'indented',
            self::PARAM_XDEBUG_DISABLE => true,
            self::PARAM_COMMAND => null,
            self::PARAM_MIN_MEMORY_LIMIT => 1610612736,
            self::PARAM_SCHEMA => '',
            self::PARAM_PROJECT_CONFIG_CANDIDATES => [],
            self::PARAM_TRUST => new Trust([], null),
            self::PARAM_TRUSTED => false,
        ]);
        $schema->setDescriptions([
            self::PARAM_XDEBUG_DISABLE => 'If XDebug should be automatically disabled',
            self::PARAM_COMMAND => 'Internal use only - name of the command which was executed',
            self::PARAM_DUMPER => 'Name of the "dumper" (renderer) to use for some CLI commands',
            self::PARAM_MIN_MEMORY_LIMIT => 'Ensure that PHP has a memory_limit of at least this amount in bytes',
            self::PARAM_SCHEMA => 'Path to JSON schema, which can be used for config autocompletion, use phpactor config:initialize to update',
            self::PARAM_PROJECT_CONFIG_CANDIDATES => '(internal) list of potential project-level configuration files',
            self::PARAM_TRUST => '(internal) map of trusted project directories',
            self::PARAM_TRUSTED => '(internal) if the configuration is trusted',
        ]);
    }

    public function load(ContainerBuilder $container): void
    {
        $this->registerConsole($container);
        $this->registerApplicationServices($container);
        $this->registerRpc($container);
        $this->registerFilePathExpanders($container);
    }

    private function registerConsole(ContainerBuilder $container): void
    {
        $container->register('command.config_dump', function (Container $container) {
            return new ConfigDumpCommand(
                $container->getParameters(),
                $container->expect('console.dumper_registry', DumperRegistry::class),
                $container->expect('config_loader.candidates', PathCandidates::class),
                $container->expect(FilePathResolverExtension::SERVICE_EXPANDERS, Expanders::class)
            );
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => 'config:dump']]);

        $container->register('command.debug_container', function (Container $container) {
            return new DebugContainerCommand(
                $container
            );
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => 'container:dump']]);

        $container->register('command.trust', function (Container $container) {
            return new TrustCommand(
                /** @phpstan-ignore argument.type */
                $container->parameter(self::PARAM_TRUST)->value(),
                $container->parameter(FilePathResolverExtension::PARAM_PROJECT_ROOT)->string(),
            );
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => 'config:trust']]);

        $container->register('command.cache_clear', function (Container $container) {
            return new CacheClearCommand(
                $container->get('application.cache_clear')
            );
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => 'cache:clear' ]]);

        $container->register('command.status', function (Container $container) {
            return new StatusCommand(
                $container->get('application.status'),
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

    private function registerApplicationServices(ContainerBuilder $container): void
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
            /** @var PathCandidates $paths */
            $paths = $container->has('config_loader.candidates') ? $container->get('config_loader.candidates') : new PathCandidates([]);

            return new Status(
                registry: $container->expect('source_code_filesystem.registry', FilesystemRegistry::class),
                // candidates are bootstrapped outside of the extensions and are not loaded in the language server
                paths: $paths,
                workingDirectory: $container->expect(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER, PathResolver::class)->resolve('%project_root%'),
                phpVersionResolver: $container->get(PhpVersionResolver::class),
                /** @phpstan-ignore argument.type */
                trust: $container->parameter(self::PARAM_TRUST)->value(),
            );
        });
    }

    private function registerRpc(ContainerBuilder $container): void
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

        $container->register('core.rpc.handler.trust', function (Container $container) {
            return new TrustHandler(
                /** @phpstan-ignore argument.type */
                $container->parameter(self::PARAM_TRUST)->value(),
                $container->parameter(FilePathResolverExtension::PARAM_PROJECT_ROOT)->string(),
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => TrustHandler::NAME] ]);
    }

    private function registerFilePathExpanders(ContainerBuilder $container): void
    {
        $container->register('core.file_path_resolver.project_config_expander', function (Container $container) {
            $path = $container->getParameter(FilePathResolverExtension::PARAM_PROJECT_ROOT) . '/.phpactor';
            return new ValueExpander('project_config', $path);
        }, [ FilePathResolverExtension::TAG_EXPANDER => [] ]);
    }
}
