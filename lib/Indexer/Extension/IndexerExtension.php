<?php

namespace Phpactor\Indexer\Extension;

use Phpactor\AmpFsWatch\Watcher;
use Phpactor\AmpFsWatch\WatcherConfig;
use Phpactor\AmpFsWatch\Watcher\BufferedWatcher\BufferedWatcher;
use Phpactor\AmpFsWatch\Watcher\Fallback\FallbackWatcher;
use Phpactor\AmpFsWatch\Watcher\Find\FindWatcher;
use Phpactor\AmpFsWatch\Watcher\FsWatch\FsWatchWatcher;
use Phpactor\AmpFsWatch\Watcher\Inotify\InotifyWatcher;
use Phpactor\AmpFsWatch\Watcher\Null\NullWatcher;
use Phpactor\AmpFsWatch\Watcher\PatternMatching\PatternMatchingWatcher;
use Phpactor\AmpFsWatch\Watcher\PhpPollWatcher\PhpPollWatcher;
use Phpactor\AmpFsWatch\Watcher\Watchman\WatchmanWatcher;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\ReferenceFinder\ReferenceFinderExtension;
use Phpactor\Extension\Rpc\RpcExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\FilePathResolverExtension\FilePathResolverExtension;
use Phpactor\FilePathResolver\PathResolver;
use Phpactor\Indexer\Adapter\ReferenceFinder\IndexedNameSearcher;
use Phpactor\Indexer\Adapter\ReferenceFinder\Util\ContainerTypeResolver;
use Phpactor\Indexer\Adapter\Worse\IndexerClassSourceLocator;
use Phpactor\Indexer\Adapter\Worse\IndexerFunctionSourceLocator;
use Phpactor\Indexer\Adapter\ReferenceFinder\IndexedReferenceFinder;
use Phpactor\Indexer\Adapter\Worse\WorseRecordReferenceEnhancer;
use Phpactor\Indexer\Extension\Command\IndexSearchCommand;
use Phpactor\Indexer\IndexAgent;
use Phpactor\Indexer\IndexAgentBuilder;
use Phpactor\Indexer\Model\IndexAccess;
use Phpactor\MapResolver\Resolver;
use Phpactor\Indexer\Adapter\ReferenceFinder\IndexedImplementationFinder;
use Phpactor\Indexer\Extension\Command\IndexQueryCommand;
use Phpactor\Indexer\Extension\Command\IndexBuildCommand;
use Phpactor\Indexer\Extension\Rpc\IndexHandler;
use Phpactor\Indexer\Model\QueryClient;
use Phpactor\Indexer\Model\SearchClient;
use Phpactor\Indexer\Model\Indexer;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\ReflectorBuilder;
use RuntimeException;
use Webmozart\PathUtil\Path;

class IndexerExtension implements Extension
{
    public const PARAM_INDEX_PATH = 'indexer.index_path';
    public const PARAM_INDEXER_POLL_TIME = 'indexer.poll_time';
    public const PARAM_ENABLED_WATCHERS = 'indexer.enabled_watchers';
    public const PARAM_INCLUDE_PATTERNS = 'indexer.include_patterns';
    public const PARAM_EXCLUDE_PATTERNS = 'indexer.exclude_patterns';
    public const PARAM_INDEXER_BUFFER_TIME = 'indexer.buffer_time';
    public const PARAM_REFERENCES_DEEP_REFERENCES = 'indexer.reference_finder.deep';
    public const PARAM_IMPLEMENTATIONS_DEEP_REFERENCES = 'indexer.implementation_finder.deep';
    public const PARAM_STUB_PATHS = 'indexer.stub_paths';

    const TAG_WATCHER = 'indexer.watcher';

    private const SERVICE_INDEXER_EXCLUDE_PATTERNS = 'indexer.exclude_patterns';
    private const SERVICE_INDEXER_INCLUDE_PATTERNS = 'indexer.include_patterns';
    private const INDEXER_TOLERANT = 'tolerant';
    private const INDEXER_WORSE = 'worse';
    private const SERVICE_FILESYSTEM = 'indexer.filesystem';
    private const PARAM_PROJECT_ROOT = 'indexer.project_root';

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_ENABLED_WATCHERS => ['inotify', 'watchman', 'find', 'php'],
            self::PARAM_INDEX_PATH => '%cache%/index/%project_id%',
            self::PARAM_INCLUDE_PATTERNS => [
                '/**/*.php',
            ],
            self::PARAM_EXCLUDE_PATTERNS => [
                '/vendor/**/Tests/**/*',
                '/vendor/**/tests/**/*',
                '/vendor/composer/**/*',
            ],
            self::PARAM_STUB_PATHS => [],
            self::PARAM_INDEXER_POLL_TIME => 5000,
            self::PARAM_INDEXER_BUFFER_TIME => 500,
            self::PARAM_PROJECT_ROOT => '%project_root%',
            self::PARAM_REFERENCES_DEEP_REFERENCES => true,
            self::PARAM_IMPLEMENTATIONS_DEEP_REFERENCES => true,
        ]);
        $schema->setDescriptions([
            self::PARAM_ENABLED_WATCHERS => 'List of allowed watchers. The first watcher that supports the current system will be used',
            self::PARAM_INDEX_PATH => 'Path where the index should be saved',
            self::PARAM_STUB_PATHS => 'Paths to external folders to index. They will be indexed only once, if you want to take any changes into account you will have to reindex your project manually.',
            self::PARAM_INCLUDE_PATTERNS => 'Glob patterns to include while indexing',
            self::PARAM_EXCLUDE_PATTERNS => 'Glob patterns to exclude while indexing',
            self::PARAM_INDEXER_POLL_TIME => 'For polling indexers only: the time, in milliseconds, between polls (e.g. filesystem scans)',
            self::PARAM_INDEXER_BUFFER_TIME => 'For real-time indexers only: the time, in milliseconds, to buffer the results',
            self::PARAM_PROJECT_ROOT => 'The root path to use for scanning the index',
            self::PARAM_REFERENCES_DEEP_REFERENCES => 'Recurse over class implementations to resolve all references',
            self::PARAM_IMPLEMENTATIONS_DEEP_REFERENCES => 'Recurse over class implementations to resolve all class implementations (not just the classes directly implementing the subject)',
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container): void
    {
        $this->registerCommands($container);
        $this->registerModel($container);
        $this->registerWorseAdapters($container);
        $this->registerRpc($container);
        $this->registerReferenceFinderAdapters($container);
        $this->registerWatcher($container);
    }

    private function createReflector(Container $container): Reflector
    {
        $builder = ReflectorBuilder::create();
        foreach (array_keys($container->getServiceIdsForTag(WorseReflectionExtension::TAG_SOURCE_LOCATOR)) as $serviceId) {
            $builder->addLocator($container->get($serviceId), 128);
        }
        $builder->enableCache();

        return $builder->build();
    }

    private function registerWorseAdapters(ContainerBuilder $container): void
    {
        $container->register(IndexerClassSourceLocator::class, function (Container $container) {
            return new IndexerClassSourceLocator($container->get(IndexAccess::class));
        }, [
            WorseReflectionExtension::TAG_SOURCE_LOCATOR => [
                'priority' => 128,
            ]
        ]);

        $container->register(IndexerFunctionSourceLocator::class, function (Container $container) {
            return new IndexerFunctionSourceLocator($container->get(IndexAccess::class));
        }, [
            WorseReflectionExtension::TAG_SOURCE_LOCATOR => [
                'priority' => 128,
            ],
        ]);
    }

    private function registerCommands(ContainerBuilder $container): void
    {
        $container->register(IndexBuildCommand::class, function (Container $container) {
            return new IndexBuildCommand(
                $container->get(Indexer::class),
                $container->get(Watcher::class)
            );
        }, [ ConsoleExtension::TAG_COMMAND => ['name' => 'index:build']]);
        
        $container->register(IndexQueryCommand::class, function (Container $container) {
            return new IndexQueryCommand($container->get(QueryClient::class));
        }, [ ConsoleExtension::TAG_COMMAND => ['name' => 'index:query']]);

        $container->register(IndexSearchCommand::class, function (Container $container) {
            return new IndexSearchCommand($container->get(SearchClient::class));
        }, [ ConsoleExtension::TAG_COMMAND => ['name' => 'index:search']]);
    }

    private function registerModel(ContainerBuilder $container): void
    {
        $container->register(IndexAgent::class, function (Container $container) {
            return $container->get(IndexAgentBuilder::class)
                ->setReferenceEnhancer($container->get(WorseRecordReferenceEnhancer::class))
                ->buildAgent();
        });

        $container->register(IndexAccess::class, function (Container $container) {
            // the worse reflection locators would have a circular reference so
            // we create a new instance for them.
            return $container->get(IndexAgentBuilder::class)
                ->buildAgent()->access();
        });

        $container->register(QueryClient::class, function (Container $container) {
            return $container->get(IndexAgent::class)->query();
        });

        $container->register(SearchClient::class, function (Container $container) {
            return $container->get(IndexAgent::class)->search();
        });

        $container->register(IndexAgentBuilder::class, function (Container $container) {
            $resolver = $container->get(
                FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER
            );
            $indexPath = $resolver->resolve(
                $container->getParameter(self::PARAM_INDEX_PATH)
            );
            return IndexAgentBuilder::create($indexPath, $this->projectRoot($container))
                ->setExcludePatterns($container->get(self::SERVICE_INDEXER_EXCLUDE_PATTERNS))
                ->setIncludePatterns(
                    $container->get(self::SERVICE_INDEXER_INCLUDE_PATTERNS),
                )
                ->setStubPaths($container->getParameter(self::PARAM_STUB_PATHS));
        });

        $container->register(Indexer::class, function (Container $container) {
            return $container->get(IndexAgent::class)->indexer();
        });

        $container->register(self::SERVICE_INDEXER_EXCLUDE_PATTERNS, function (Container $container) {
            $projectRoot = $this->projectRoot($container);
            return array_map(function (string $pattern) use ($projectRoot) {
                return Path::join([$projectRoot, $pattern]);
            }, $container->getParameter(self::PARAM_EXCLUDE_PATTERNS));
        });

        $container->register(self::SERVICE_INDEXER_INCLUDE_PATTERNS, function (Container $container) {
            $projectRoot = $container->getParameter(FilePathResolverExtension::PARAM_PROJECT_ROOT);

            return array_map(function (string $pattern) use ($projectRoot) {
                return Path::join([$projectRoot, $pattern]);
            }, $container->getParameter(self::PARAM_INCLUDE_PATTERNS));
        });
        
        $container->register(WorseRecordReferenceEnhancer::class, function (Container $container) {
            return new WorseRecordReferenceEnhancer(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get(LoggingExtension::SERVICE_LOGGER)
            );
        });
    }

    private function registerReferenceFinderAdapters(ContainerBuilder $container): void
    {
        $container->register(IndexedImplementationFinder::class, function (Container $container) {
            return new IndexedImplementationFinder(
                $container->get(QueryClient::class),
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->getParameter(self::PARAM_IMPLEMENTATIONS_DEEP_REFERENCES)
            );
        }, [ ReferenceFinderExtension::TAG_IMPLEMENTATION_FINDER => []]);

        $container->register(IndexedReferenceFinder::class, function (Container $container) {
            return new IndexedReferenceFinder(
                $container->get(QueryClient::class),
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                new ContainerTypeResolver($container->get(WorseReflectionExtension::SERVICE_REFLECTOR)),
                $container->getParameter(self::PARAM_REFERENCES_DEEP_REFERENCES)
            );
        }, [ ReferenceFinderExtension::TAG_REFERENCE_FINDER => []]);

        $container->register(IndexedNameSearcher::class, function (Container $container) {
            return new IndexedNameSearcher(
                $container->get(SearchClient::class)
            );
        }, [ ReferenceFinderExtension::TAG_NAME_SEARCHER => []]);
    }

    private function registerRpc(ContainerBuilder $container): void
    {
        if (!class_exists(RpcExtension::class)) {
            return;
        }

        $container->register(IndexHandler::class, function (Container $container) {
            return new IndexHandler(
                $container->get(Indexer::class),
                $container->get(Watcher::class)
            );
        }, [
            RpcExtension::TAG_RPC_HANDLER => [
                'name' => IndexHandler::NAME,
            ],
        ]);
    }

    private function registerWatcher(ContainerBuilder $container): void
    {
        $container->register(Watcher::class, function (Container $container) {
            $watchers = [];

            $enabledWatchers = $container->getParameter(self::PARAM_ENABLED_WATCHERS);

            foreach ($container->getServiceIdsForTag(self::TAG_WATCHER) as $serviceId => $attrs) {
                if (!isset($attrs['name'])) {
                    throw new RuntimeException(sprintf(
                        'Watcher "%s" must provide the `name` attribute',
                        $serviceId
                    ));
                }

                $watchers[$attrs['name']] = $serviceId;
            }

            if ($diff = array_diff($enabledWatchers, array_keys($watchers))) {
                throw new RuntimeException(sprintf(
                    'Unknown watchers "%s" specified, available watchers: "%s"',
                    implode('", "', $diff),
                    implode('", "', array_keys($watchers))
                ));
            }

            $watchers = (function (Container $container, array $watchers, array $enabledWatchers) {
                $filtered = [];
                foreach ($watchers as $name => $serviceId) {
                    if (!in_array($name, $enabledWatchers)) {
                        continue;
                    }

                    $filtered[$name] = $container->get($serviceId);
                };

                $ordered = [];
                foreach ($enabledWatchers as $enabledWatcher) {
                    $ordered[] = $filtered[$enabledWatcher];
                }

                return $ordered;
            })($container, $watchers, $enabledWatchers);

            if ($watchers === []) {
                return new NullWatcher();
            }

            return new PatternMatchingWatcher(
                new FallbackWatcher($watchers, $container->get(LoggingExtension::SERVICE_LOGGER)),
                $container->get(self::SERVICE_INDEXER_INCLUDE_PATTERNS),
                $container->get(self::SERVICE_INDEXER_EXCLUDE_PATTERNS)
            );
        });
        $container->register(WatcherConfig::class, function (Container $container) {
            $resolver = $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER);
            assert($resolver instanceof PathResolver);

            // NOTE: the project root should NOT have a scheme in it (file://), but there is no validation
            // about this, so we parse it using the text document URI
            $path = TextDocumentUri::fromString($resolver->resolve('%project_root%'));

            return new WatcherConfig([
                $path->path()
            ], $container->getParameter(self::PARAM_INDEXER_POLL_TIME));
        });

        // register watchers - order of registration currently determines
        // priority

        $container->register(WatchmanWatcher::class, function (Container $container) {
            return new BufferedWatcher(new WatchmanWatcher(
                $container->get(WatcherConfig::class),
                $container->get(LoggingExtension::SERVICE_LOGGER)
            ), $container->getParameter(self::PARAM_INDEXER_BUFFER_TIME));
        }, [
            self::TAG_WATCHER => [
                'name' => 'watchman',
            ]
        ]);

        $container->register(InotifyWatcher::class, function (Container $container) {
            return new BufferedWatcher(new InotifyWatcher(
                $container->get(WatcherConfig::class),
                $container->get(LoggingExtension::SERVICE_LOGGER)
            ), $container->getParameter(self::PARAM_INDEXER_BUFFER_TIME));
        }, [
            self::TAG_WATCHER => [
                'name' => 'inotify',
            ]
        ]);

        $container->register(FsWatchWatcher::class, function (Container $container) {
            return new FsWatchWatcher(
                $container->get(WatcherConfig::class),
                $container->get(LoggingExtension::SERVICE_LOGGER)
            );
        }, [
            self::TAG_WATCHER => [
                'name' => 'fswatch',
            ]
        ]);

        $container->register(FindWatcher::class, function (Container $container) {
            return new FindWatcher(
                $container->get(WatcherConfig::class),
                $container->get(LoggingExtension::SERVICE_LOGGER)
            );
        }, [
            self::TAG_WATCHER => [
                'name' => 'find',
            ]
        ]);

        $container->register(PhpPollWatcher::class, function (Container $container) {
            return new PhpPollWatcher(
                $container->get(WatcherConfig::class),
                $container->get(LoggingExtension::SERVICE_LOGGER)
            );
        }, [
            self::TAG_WATCHER => [
                'name' => 'php',
            ]
        ]);
    }

    private function projectRoot(Container $container): string
    {
        return $container->get(
            FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER
        )->resolve($container->getParameter(self::PARAM_PROJECT_ROOT));
    }
}
