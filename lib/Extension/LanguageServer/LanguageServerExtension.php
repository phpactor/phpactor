<?php

namespace Phpactor\Extension\LanguageServer;

use Composer\InstalledVersions;
use Phly\EventDispatcher\EventDispatcher;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Core\CoreExtension as PhpactorCoreExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\Extension\LanguageServerWorseReflection\Workspace\WorkspaceIndex;
use Phpactor\Extension\LanguageServer\CodeAction\ProfilingCodeActionProvider;
use Phpactor\Extension\LanguageServer\CodeAction\TolerantCodeActionProvider;
use Phpactor\Extension\LanguageServer\Command\DiagnosticsCommand;
use Phpactor\Extension\LanguageServer\DiagnosticProvider\AggregateDiagnosticsProvider;
use Phpactor\Extension\LanguageServer\DiagnosticProvider\CodeFilteringDiagnosticProvider;
use Phpactor\Extension\LanguageServer\DiagnosticProvider\OutsourcedDiagnosticsProvider;
use Phpactor\Extension\LanguageServer\DiagnosticProvider\PathExcludingDiagnosticsProvider;
use Phpactor\Extension\LanguageServer\Dispatcher\PhpactorDispatcherFactory;
use Phpactor\Extension\LanguageServer\EventDispatcher\LazyAggregateProvider;
use Phpactor\Extension\LanguageServer\Handler\DebugHandler;
use Phpactor\Extension\LanguageServer\Listener\InvalidConfigListener;
use Phpactor\Extension\LanguageServer\Listener\ProjectConfigTrustListener;
use Phpactor\Extension\LanguageServer\Listener\SelfDestructListener;
use Phpactor\Extension\LanguageServer\Logger\ClientLogger;
use Phpactor\Extension\LanguageServer\Middleware\ProfilerMiddleware;
use Phpactor\Extension\LanguageServer\Middleware\TraceMiddleware;
use Phpactor\Extension\LanguageServer\Container\DiagnosticProviderTag;
use Phpactor\Extension\LanguageServer\Status\StatsStatusProvider;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\LanguageServer\Command\StartCommand;
use Phpactor\FilePathResolver\PathResolver;
use Phpactor\LanguageServerProtocol\ClientCapabilities;
use Phpactor\LanguageServer\Core\CodeAction\AggregateCodeActionProvider;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use Phpactor\LanguageServer\Core\Command\CommandDispatcher;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsEngine;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use Phpactor\LanguageServer\Diagnostics\CodeActionDiagnosticsProvider;
use Phpactor\LanguageServer\Handler\System\StatsHandler;
use Phpactor\LanguageServer\Handler\TextDocument\CodeActionHandler;
use Phpactor\LanguageServer\Handler\TextDocument\FormattingHandler;
use Phpactor\LanguageServer\Handler\TextDocument\TextDocumentHandler;
use Phpactor\LanguageServer\Core\Handler\HandlerMethodRunner;
use Phpactor\LanguageServer\Adapter\DTL\DTLArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\LanguageSeverProtocolParamsResolver;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\ChainArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver;
use Phpactor\LanguageServer\Handler\Workspace\DidChangeWatchedFilesHandler;
use Phpactor\LanguageServer\Listener\DidChangeWatchedFilesListener;
use Phpactor\LanguageServer\Middleware\HandlerMiddleware;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher;
use Phpactor\LanguageServer\Middleware\ResponseHandlingMiddleware;
use Phpactor\LanguageServer\Middleware\MethodAliasMiddleware;
use Phpactor\LanguageServer\Core\Handler\MethodRunner;
use Phpactor\LanguageServer\Middleware\CancellationMiddleware;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Middleware\InitializeMiddleware;
use Phpactor\LanguageServer\Middleware\ErrorHandlingMiddleware;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\MiddlewareDispatcher;
use Phpactor\LanguageServer\Core\Service\ServiceProvider;
use Phpactor\LanguageServer\Core\Service\ServiceProviders;
use Phpactor\LanguageServer\Listener\ServiceListener;
use Phpactor\LanguageServer\Handler\Workspace\CommandHandler;
use Phpactor\LanguageServer\Core\Service\ServiceManager;
use Phpactor\LanguageServer\Handler\System\ServiceHandler;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Listener\WorkspaceListener;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\LanguageServer\LanguageServerBuilder;
use Phpactor\LanguageServer\Core\Server\ServerStats;
use Phpactor\LanguageServer\Middleware\ShutdownMiddleware;
use Phpactor\LanguageServer\Service\DiagnosticsService;
use Phpactor\LanguageServer\WorkDoneProgress\ProgressNotifier;
use Phpactor\MapResolver\Resolver;
use Phpactor\MapResolver\ResolverErrors;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use SplPriorityQueue;
use Symfony\Component\Filesystem\Path;
use Webmozart\Assert\Assert;
use function array_filter;
use function array_keys;
use function array_values;
use function get_debug_type;
use function implode;
use function is_string;
use function sprintf;

class LanguageServerExtension implements Extension
{
    public const SERVICE_LANGUAGE_SERVER_BUILDER = 'language_server.builder';
    public const SERVICE_EVENT_EMITTER = 'language_server.event_emitter';
    public const SERVICE_SESSION_WORKSPACE = 'language_server.session.workspace';
    public const TAG_METHOD_HANDLER = 'language_server.session_handler';
    public const TAG_COMMAND = 'language_server.command';
    public const TAG_SERVICE_PROVIDER = 'language_server.service_provider';
    public const TAG_LISTENER_PROVIDER = 'language_server.listener_provider';
    public const TAG_CODE_ACTION_PROVIDER = 'language_server.code_action_provider';
    public const TAG_CODE_ACTION_DIAGNOSTICS_PROVIDER = 'language_server.code_action_diagnostics_provider';
    public const TAG_STATUS_PROVIDER = 'language_server.status_provvder';
    public const TAG_DIAGNOSTICS_PROVIDER = 'language_server.diagnostics_provider';
    public const TAG_MIDDLEWARE = 'language_server.middleware';
    public const TAG_FORMATTER = 'language_server.formatter';
    public const PARAM_SESSION_PARAMETERS = 'language_server.session_parameters';
    public const PARAM_CLIENT_CAPABILITIES = 'language_server.client_capabilities';
    public const PARAM_ENABLE_WORKPACE = 'language_server.enable_workspace';
    public const PARAM_CATCH_ERRORS = 'language_server.catch_errors';
    public const PARAM_METHOD_ALIAS_MAP = 'language_server.method_alias_map';
    public const PARAM_DIAGNOSTIC_SLEEP_TIME = 'language_server.diagnostic_sleep_time';
    public const PARAM_DIAGNOSTIC_ON_UPDATE = 'language_server.diagnostics_on_update';
    public const PARAM_DIAGNOSTIC_ON_SAVE = 'language_server.diagnostics_on_save';
    public const PARAM_DIAGNOSTIC_ON_OPEN = 'language_server.diagnostics_on_open';
    public const PARAM_DIAGNOSTIC_PROVIDERS = 'language_server.diagnostic_providers';
    public const PARAM_DIAGNOSTIC_OUTSOURCE = 'language_server.diagnostic_outsource';
    public const PARAM_FILE_EVENTS = 'language_server.file_events';
    public const PARAM_FILE_EVENT_GLOBS = 'language_server.file_event_globs';
    public const PARAM_PROFILE = 'language_server.profile';
    public const PARAM_TRACE = 'language_server.trace';
    public const LOG_CHANNEL = 'LSP';
    public const PARAM_SHUTDOWN_GRACE_PERIOD = 'language_server.shutdown_grace_period';
    public const PARAM_SELF_DESTRUCT_TIMEOUT = 'language_server.self_destruct_timeout';
    public const PARAM_PHPACTOR_BIN = 'language_server.phpactor_bin';
    public const PARAM_DIAGNOSTIC_OUTSOURCE_TIMEOUT = 'language_server.diagnostic_outsource_timeout';
    public const PARAM_DIAGNOSTIC_EXCLUDE_PATHS = 'language_server.diagnostic_exclude_paths';
    public const PARAM_DIAGNOSTIC_IGNORE_CODES = 'language_server.diagnostic_ignore_codes';
    public const PARAM_ENABLE_TRUST_CHECK = 'language_server.enable_trust_check';

    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_CATCH_ERRORS => true,
            self::PARAM_ENABLE_WORKPACE => true,
            self::PARAM_SESSION_PARAMETERS => [],
            self::PARAM_METHOD_ALIAS_MAP => [],
            self::PARAM_DIAGNOSTIC_SLEEP_TIME => 1000,
            self::PARAM_DIAGNOSTIC_ON_UPDATE => true,
            self::PARAM_DIAGNOSTIC_ON_SAVE => true,
            self::PARAM_DIAGNOSTIC_ON_OPEN => true,
            self::PARAM_DIAGNOSTIC_PROVIDERS => null,
            self::PARAM_DIAGNOSTIC_OUTSOURCE => true,
            self::PARAM_DIAGNOSTIC_EXCLUDE_PATHS => [],
            self::PARAM_DIAGNOSTIC_IGNORE_CODES => [],
            self::PARAM_ENABLE_TRUST_CHECK => true,
            self::PARAM_FILE_EVENTS => true,
            self::PARAM_FILE_EVENT_GLOBS => ['**/*.php'],
            self::PARAM_PROFILE => false,
            self::PARAM_TRACE => false,
            self::PARAM_SHUTDOWN_GRACE_PERIOD => 200,
            self::PARAM_PHPACTOR_BIN => __DIR__ . '/../../../bin/phpactor',
            self::PARAM_SELF_DESTRUCT_TIMEOUT => 2500,
            self::PARAM_DIAGNOSTIC_OUTSOURCE_TIMEOUT => 5,
        ]);
        $schema->setDescriptions([
            self::PARAM_ENABLE_TRUST_CHECK => 'Check to see if project path is trusted before loading configurations from it',
            self::PARAM_TRACE => 'Log incoming and outgoing messages (needs log formatter to be set to ``json``)',
            self::PARAM_PROFILE => 'Logs timing information for incoming LSP requests',
            self::PARAM_METHOD_ALIAS_MAP => 'Allow method names to be re-mapped. Useful for maintaining backwards compatibility',
            self::PARAM_SESSION_PARAMETERS => 'Phpactor parameters (config) that apply only to the language server session',
            self::PARAM_ENABLE_WORKPACE => <<<'EOT'
                If workspace management / text synchronization should be enabled (this isn't required for some language server implementations, e.g. static analyzers)
                EOT
            ,
            self::PARAM_DIAGNOSTIC_SLEEP_TIME => 'Amount of time to wait before analyzing the code again for diagnostics',
            self::PARAM_DIAGNOSTIC_ON_UPDATE => 'Perform diagnostics when the text document is updated',
            self::PARAM_DIAGNOSTIC_ON_SAVE => 'Perform diagnostics when the text document is saved',
            self::PARAM_DIAGNOSTIC_ON_OPEN => 'Perform diagnostics when opening a text document',
            self::PARAM_DIAGNOSTIC_PROVIDERS => 'Specify which diagnostic providers should be active (default to all)',
            self::PARAM_DIAGNOSTIC_OUTSOURCE => 'If applicable diagnostics should be "outsourced" to a different process',
            self::PARAM_DIAGNOSTIC_OUTSOURCE_TIMEOUT => 'Kill the diagnostics process if it outlives this timeout',
            self::PARAM_DIAGNOSTIC_IGNORE_CODES => 'Ignore diagnostics that have the codes listed here, e.g. ["fix_namespace_class_name"]. The codes match those shown in the LSP client.',
            self::PARAM_FILE_EVENTS => 'Register to receive file events',
            self::PARAM_DIAGNOSTIC_EXCLUDE_PATHS => 'List of paths to exclude from diagnostics, e.g. `vendor/**/*`',
            self::PARAM_SHUTDOWN_GRACE_PERIOD => 'Amount of time (in milliseconds) to wait before responding to a shutdown notification',
            self::PARAM_SELF_DESTRUCT_TIMEOUT => 'Wait this amount of time (in milliseconds) after a shutdown request before self-destructing',
            self::PARAM_PHPACTOR_BIN => 'Internal use only - name path to Phpactor binary',
        ]);
    }


    public function load(ContainerBuilder $container): void
    {
        $this->registerServer($container);
        $this->registerCommand($container);
        $this->registerSession($container);
        $this->registerEventDispatcher($container);
        $this->registerCommandDispatcher($container);
        $this->registerServiceManager($container);
        $this->registerMiddleware($container);
        $this->registerDiagnostics($container);
        $this->registerHandlers($container);
        $this->registerServices($container);
    }

    private function registerServer(ContainerBuilder $container): void
    {
        $container->register(ServerStats::class, function (Container $container) {
            return new ServerStats();
        });

        $container->register(LanguageServerBuilder::class, function (Container $container) {
            $builder = LanguageServerBuilder::create(
                new PhpactorDispatcherFactory($container),
                $this->logger($container)
            );

            return $builder;
        });

        $container->register(ClientLogger::class, function (Container $container) {
            return new ClientLogger(
                $container->get(ClientApi::class),
                $this->logger($container),
            );
        });
    }

    private function registerCommand(ContainerBuilder $container): void
    {
        if (!class_exists(ConsoleExtension::class)) {
            return;
        }

        $container->register('language_server.command.lsp_start', function (Container $container) {
            return new StartCommand($container->get(LanguageServerBuilder::class));
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => StartCommand::NAME ]]);

        $container->register(DiagnosticsCommand::class, function (Container $container) {
            /** @var AggregateDiagnosticsProvider $provider */
            $provider = $container->get(AggregateDiagnosticsProvider::class . '.outsourced');
            return new DiagnosticsCommand(
                $provider,
                $container->get(WorkspaceIndex::class),
            );
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => DiagnosticsCommand::NAME ]]);
    }

    private function registerSession(ContainerBuilder $container): void
    {
        $container->register(self::SERVICE_SESSION_WORKSPACE, function (Container $container) {
            return new Workspace($this->logger($container));
        });

        $container->register(WorkspaceListener::class, function (Container $container) {
            if ($container->parameter(self::PARAM_ENABLE_WORKPACE)->bool() === false) {
                return null;
            }

            return new WorkspaceListener($this->workspace($container));
        }, [
            self::TAG_LISTENER_PROVIDER => [],
        ]);

        $container->register(InvalidConfigListener::class, function (Container $container) {
            return new InvalidConfigListener(
                $container->get(ClientApi::class),
                $container->has(ResolverErrors::class) ? $container->get(ResolverErrors::class) : new ResolverErrors([])
            );
        }, [
            self::TAG_LISTENER_PROVIDER => [],
        ]);

        $container->register(ProjectConfigTrustListener::class, function (Container $container) {
            if (false === $container->parameter(self::PARAM_ENABLE_TRUST_CHECK)->bool()) {
                return null;
            }
            return new ProjectConfigTrustListener(
                $container->get(ClientApi::class),
                $container->parameter(PhpactorCoreExtension::PARAM_PROJECT_CONFIG_CANDIDATES)->listOfString(),
                /** @phpstan-ignore argument.type */
                $container->parameter(PhpactorCoreExtension::PARAM_TRUST)->value(),
            );
        }, [
            self::TAG_LISTENER_PROVIDER => [],
        ]);

        $container->register(SelfDestructListener::class, function (Container $container) {
            return new SelfDestructListener($container->parameter(self::PARAM_SELF_DESTRUCT_TIMEOUT)->int());
        }, [
            self::TAG_LISTENER_PROVIDER => [],
        ]);

        $container->register(DidChangeWatchedFilesListener::class, function (Container $container) {
            return new DidChangeWatchedFilesListener(
                $container->get(ClientApi::class),
                /** @phpstan-ignore-next-line */
                $container->parameter(self::PARAM_FILE_EVENT_GLOBS)->value(),
                $container->get(ClientCapabilities::class),
            );
        }, [
            self::TAG_LISTENER_PROVIDER => [],
        ]);

        $container->register(StatsStatusProvider::class, function (Container $container) {
            return new StatsStatusProvider();
        }, [
            LanguageServerExtension::TAG_STATUS_PROVIDER => [],
        ]);

        $container->register('language_server.session.handler.session', function (Container $container) {
            $providers = [];
            foreach ($container->getServiceIdsForTag(self::TAG_STATUS_PROVIDER) as $serviceId => $_) {
                $providers[] = $container->get($serviceId);
            }
            return new DebugHandler(
                $container,
                $container->get(ClientApi::class),
                $this->workspace($container),
                $container->get(ServerStats::class),
                $container->get(ServiceManager::class),
                $container->get(AggregateDiagnosticsProvider::class),
                $providers
            );
        }, [ self::TAG_METHOD_HANDLER => []]);

        $container->register(ServiceHandler::class, function (Container $container) {
            return new ServiceHandler($container->get(ServiceManager::class), $container->get(ClientApi::class));
        }, [ self::TAG_METHOD_HANDLER => []]);

        $container->register(CommandHandler::class, function (Container $container) {
            return new CommandHandler($container->get(CommandDispatcher::class));
        }, [ self::TAG_METHOD_HANDLER => []]);

        $container->register(DidChangeWatchedFilesHandler::class, function (Container $container) {
            return new DidChangeWatchedFilesHandler($container->get(EventDispatcherInterface::class));
        }, [
            self::TAG_METHOD_HANDLER => [],
        ]);
    }

    private function registerEventDispatcher(ContainerBuilder $container): void
    {
        $container->register(EventDispatcherInterface::class, function (Container $container) {
            $aggregate = new LazyAggregateProvider(
                $container,
                $this->resolveListeners($container)
            );

            return new EventDispatcher($aggregate);
        });
    }

    private function registerCommandDispatcher(ContainerBuilder $container): void
    {
        $container->register(CommandDispatcher::class, function (Container $container) {
            $map = [];
            foreach ($container->getServiceIdsForTag(self::TAG_COMMAND) as $serviceId => $attrs) {
                if (!isset($attrs['name'])) {
                    throw new RuntimeException(sprintf(
                        'Cannot register command with service ID "%s" Each command must define a "name" attribute',
                        $serviceId
                    ));
                }
                assert(is_string($attrs['name']));
                $map[$attrs['name']] = $container->get($serviceId);
            }

            return new CommandDispatcher($map);
        });
    }

    private function registerServiceManager(ContainerBuilder $container): void
    {
        $container->register(ServiceListener::class, function (Container $container) {
            return new ServiceListener($container->get(ServiceManager::class));
        }, [
            self::TAG_LISTENER_PROVIDER => [],
        ]);

        $container->register(ServiceManager::class, function (Container $container) {
            return new ServiceManager(
                $container->get(ServiceProviders::class),
                $container->get(ClientLogger::class)
            );
        });
        $container->register(ServiceProviders::class, function (Container $container) {
            $providers = [];
            foreach ($container->getServiceIdsForTag(self::TAG_SERVICE_PROVIDER) as $serviceId => $attrs) {
                $provider = $container->get($serviceId);
                if (!$provider instanceof ServiceProvider) {
                    throw new RuntimeException(sprintf(
                        'Tagged service provider "%s" does not implement ServiceProvider interface, is a "%s"',
                        $serviceId,
                        get_debug_type($provider),
                    ));
                }
                $providers[] = $provider;
            }

            return new ServiceProviders(...$providers);
        });
    }

    private function registerMiddleware(ContainerBuilder $container): void
    {
        $container->register(MiddlewareDispatcher::class, function (Container $container) {
            $stack = [];

            if ($container->parameter(self::PARAM_PROFILE)->bool()) {
                $stack[] = new ProfilerMiddleware($this->logger($container));
            }

            foreach ($container->getServiceIdsForTag(self::TAG_MIDDLEWARE) as $serviceId => $_) {
                $service = $container->get($serviceId);
                if (null === $service) {
                    continue;
                }
                $stack[] = $service;
            }

            if ($container->parameter(self::PARAM_TRACE)->bool()) {
                $stack[] = new TraceMiddleware($this->logger($container));
            }

            if ($container->parameter(self::PARAM_CATCH_ERRORS)->bool()) {
                $stack[] = new ErrorHandlingMiddleware($this->logger($container));
            }

            $stack[] = new InitializeMiddleware(
                $container->get(Handlers::class),
                $container->get(EventDispatcherInterface::class),
                $this->serverInfo()
            );

            $stack[] = new ShutdownMiddleware($container->get(EventDispatcherInterface::class), $container->parameter(self::PARAM_SHUTDOWN_GRACE_PERIOD)->int());
            $stack[] = new CancellationMiddleware($container->get(MethodRunner::class));

            /** @phpstan-ignore-next-line*/
            $stack[] = new MethodAliasMiddleware($container->parameter(self::PARAM_METHOD_ALIAS_MAP)->value());
            $stack[] = new ResponseHandlingMiddleware($container->get(ResponseWatcher::class));

            $stack[] = new HandlerMiddleware(
                $container->get(MethodRunner::class)
            );


            return new MiddlewareDispatcher(...$stack);
        });
    }

    private function registerHandlers(ContainerBuilder $container): void
    {
        $container->register(ArgumentResolver::class, function (Container $container) {
            return new ChainArgumentResolver(
                new LanguageSeverProtocolParamsResolver(),
                new DTLArgumentResolver(),
            );
        });
        $container->register(MethodRunner::class, function (Container $container) {
            return new HandlerMethodRunner(
                $container->get(Handlers::class),
                $container->get(ArgumentResolver::class),
                $this->logger($container)
            );
        });

        $container->register(Handlers::class, function (Container $container) {
            $handlers = [];

            foreach (array_keys(
                $container->getServiceIdsForTag(LanguageServerExtension::TAG_METHOD_HANDLER)
            ) as $serviceId) {
                $handler = $container->get($serviceId);
                if (null === $handler) {
                    continue;
                }
                $handlers[] = $handler;
            }

            return new Handlers(...$handlers);
        });

        $container->register(TextDocumentHandler::class, function (Container $container) {
            return new TextDocumentHandler($container->get(EventDispatcherInterface::class));
        }, [ self::TAG_METHOD_HANDLER => []]);

        $container->register(StatsHandler::class, function (Container $container) {
            return new StatsHandler(
                $container->get(ClientApi::class),
                $container->get(ServerStats::class)
            );
        }, [ self::TAG_METHOD_HANDLER => []]);

        $container->register(CodeActionHandler::class, function (Container $container) {
            $services = new SplPriorityQueue();
            $profile = $container->parameter(self::PARAM_PROFILE)->bool();
            foreach ($container->getServiceIdsForTag(self::TAG_CODE_ACTION_PROVIDER) as $serviceId => $attributes) {
                $provider = new TolerantCodeActionProvider(
                    $container->expect($serviceId, CodeActionProvider::class),
                    $container->get(ClientApi::class),
                );
                if ($profile) {
                    $provider = new ProfilingCodeActionProvider($provider, $this->logger($container));
                }

                $services->insert($provider, $attributes['priority'] ?? 0);
            }

            return new CodeActionHandler(
                /** @phpstan-ignore-next-line */
                new AggregateCodeActionProvider(...$services),
                $this->workspace($container),
                $container->get(ProgressNotifier::class),
            );
        }, [ self::TAG_METHOD_HANDLER => []]);

        $container->register(FormattingHandler::class, function (Container $container) {
            $formatter = null;
            foreach ($container->getServiceIdsForTag(self::TAG_FORMATTER) as $seviceId => $_) {
                $formatter = $container->get($seviceId);
                if (null === $formatter) {
                    continue;
                }
                break;
            }

            if ($formatter === null) {
                return null;
            }

            return new FormattingHandler(
                $this->workspace($container),
                $formatter,
                $container->get(ProgressNotifier::class),
            );
        }, [
            LanguageServerExtension::TAG_METHOD_HANDLER => [
            ],
        ]);
    }

    private function registerServices(ContainerBuilder $container): void
    {
        $container->register(DiagnosticsService::class, function (Container $container) {
            return new DiagnosticsService(
                $container->get(DiagnosticsEngine::class),
                $container->parameter(self::PARAM_DIAGNOSTIC_ON_UPDATE)->bool(),
                $container->parameter(self::PARAM_DIAGNOSTIC_ON_SAVE)->bool(),
                $this->workspace($container),
                true,
                $container->parameter(self::PARAM_DIAGNOSTIC_ON_OPEN)->bool()
            );
        }, [
            self::TAG_SERVICE_PROVIDER => [],
            self::TAG_LISTENER_PROVIDER => [],
        ]);
    }

    private function registerDiagnostics(ContainerBuilder $container): void
    {
        $container->register(DiagnosticsEngine::class, function (Container $container) {
            $providers = $this->collectDiagnosticProviders(
                $container,
                outsourced: $container->parameter(self::PARAM_DIAGNOSTIC_OUTSOURCE)->bool() ? false : null,
            );

            $projectRoot = $container->parameter(FilePathResolverExtension::PARAM_PROJECT_ROOT)->string();

            /**
             * @var string[] $excludePaths
             */
            $excludePaths = $container->parameter(self::PARAM_DIAGNOSTIC_EXCLUDE_PATHS)->value();

            if (count($excludePaths)) {
                $providers = array_map(function (DiagnosticsProvider $provider) use ($projectRoot, $excludePaths) {
                    return new PathExcludingDiagnosticsProvider(
                        $provider,
                        // make all the exclude paths absolute before passing to the provider
                        array_map(fn (string $path) => Path::join($projectRoot, $path), $excludePaths)
                    );
                }, $providers);
            }

            $ignoreCodes = $container->parameter(self::PARAM_DIAGNOSTIC_IGNORE_CODES)->listOfString();

            if (count($ignoreCodes)) {
                $providers = array_map(function (DiagnosticsProvider $provider) use ($ignoreCodes) {
                    return new CodeFilteringDiagnosticProvider(
                        $provider,
                        $ignoreCodes,
                    );
                }, $providers);
            }

            return new DiagnosticsEngine(
                $container->get(ClientApi::class),
                $this->logger($container, 'LSPDIAG'),
                $providers,
                $container->parameter(self::PARAM_DIAGNOSTIC_SLEEP_TIME)->int()
            );
        });

        $container->register(AggregateDiagnosticsProvider::class, function (Container $container) {
            $providers = $this->collectDiagnosticProviders(
                $container,
                outsourced: $container->parameter(self::PARAM_DIAGNOSTIC_OUTSOURCE)->bool() ? false : null,
            );

            return new AggregateDiagnosticsProvider(
                $this->logger($container, 'LSPDIAG'),
                ...array_values($providers)
            );
        });

        $container->register(AggregateDiagnosticsProvider::class.'.outsourced', function (Container $container) {
            $providers = $this->collectDiagnosticProviders($container, true);

            return new AggregateDiagnosticsProvider(
                $this->logger($container, 'OUTLSPDIAG'),
                ...array_values($providers)
            );
        });

        $container->register(CodeActionDiagnosticsProvider::class, function (Container $container) {
            return new CodeActionDiagnosticsProvider(
                ...$this->taggedServices($container, self::TAG_CODE_ACTION_DIAGNOSTICS_PROVIDER, CodeActionProvider::class)
            );
        }, [
            self::TAG_DIAGNOSTICS_PROVIDER => DiagnosticProviderTag::create('code-action', outsource: true),
        ]);

        $container->register(OutsourcedDiagnosticsProvider::class, function (Container $container) {
            /** @var PathResolver $resolver */
            $resolver = $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER);
            $projectPath = $resolver->resolve('%project_root%');
            // only register this if we should call out to an external process for diagnostics
            if (!$container->parameter(self::PARAM_DIAGNOSTIC_OUTSOURCE)->bool()) {
                return null;
            }

            return new OutsourcedDiagnosticsProvider([
                $container->parameter(self::PARAM_PHPACTOR_BIN)->string(),
                'language-server:diagnostics',
            ], $projectPath, $this->logger($container), $container->parameter(self::PARAM_DIAGNOSTIC_OUTSOURCE_TIMEOUT)->int());
        }, [
            self::TAG_DIAGNOSTICS_PROVIDER => DiagnosticProviderTag::create('outsourced'),
        ]);
    }

    /**
     * @template TType
     * @param null|class-string<TType> $fqn
     * @return ($fqn is class-string<TType> ? list<TType> : list<mixed>)
     */
    private function taggedServices(Container $container, string $tag, ?string $fqn = null): array
    {
        $providers = [];
        foreach (array_keys($container->getServiceIdsForTag($tag)) as $serviceId) {
            $providers[] = $container->get($serviceId);
        }
        return $providers;
    }

    /**
     * @return string[]
     */
    private function resolveListeners(Container $container): array
    {
        return array_filter(array_keys($container->getServiceIdsForTag(self::TAG_LISTENER_PROVIDER)), function (string $service) use ($container) {
            if (false === $container->parameter(self::PARAM_FILE_EVENTS)->bool() && $service === DidChangeWatchedFilesListener::class) {
                return false;
            }
            return true;
        });
    }

    private function logger(Container $container, string $name = self::LOG_CHANNEL): LoggerInterface
    {
        return LoggingExtension::channelLogger($container, $name);
    }

    private function workspace(Container $container): Workspace
    {
        return $container->expect(self::SERVICE_SESSION_WORKSPACE, Workspace::class);
    }

    /**
     * @return array{name:string,version:string,version:string}
     */
    private function serverInfo(): array
    {
        $package = InstalledVersions::getRootPackage();
        return [
            'name' => $package['name'],
            'version' => $package['pretty_version'],
        ];
    }

    /**
     * @return DiagnosticsProvider[]
     */
    private function collectDiagnosticProviders(Container $container, ?bool $outsourced): array
    {
        $providers = [];
        foreach ($container->getServiceIdsForTag(self::TAG_DIAGNOSTICS_PROVIDER) as $serviceId => $attrs) {
            Assert::isArray($attrs, 'Attributes must be an array, got "%s"');

            if (null !== $outsourced && ($attrs[DiagnosticProviderTag::OUTSOURCE] ?? false) !== $outsourced) {
                continue;
            }

            $provider = $container->get($serviceId);

            if (null === $provider) {
                continue;
            }

            $providers[$attrs[DiagnosticProviderTag::NAME] ?? $serviceId] = $provider;
        }

        $enabled = $container->getParameter(self::PARAM_DIAGNOSTIC_PROVIDERS);

        if (null !== $enabled) {
            Assert::isArray($enabled);
            if ($diff = array_diff($enabled, array_keys($providers))) {
                throw new RuntimeException(sprintf(
                    'Unknown diagnostic provider(s) "%s", known providers: "%s"',
                    implode('", "', $diff),
                    implode('", "', array_keys($providers))
                ));
            }
            $providers = array_intersect_key($providers, array_flip($enabled));
        }

        /** @var DiagnosticsProvider[] $providers */
        return $providers;
    }
}
