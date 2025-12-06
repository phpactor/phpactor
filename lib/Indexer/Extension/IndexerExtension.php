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
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\FilePathResolver\PathResolver;
use Phpactor\Indexer\Adapter\Php\PhpIndexerLister;
use Phpactor\Indexer\Adapter\ReferenceFinder\IndexedNameSearcher;
use Phpactor\Indexer\Adapter\ReferenceFinder\Util\ContainerTypeResolver;
use Phpactor\Indexer\Adapter\Worse\IndexerClassSourceLocator;
use Phpactor\Indexer\Adapter\Worse\IndexerConstantSourceLocator;
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
use Phpactor\Indexer\Extension\Command\IndexCleanCommand;
use Phpactor\Indexer\Extension\Rpc\IndexHandler;
use Phpactor\Indexer\Model\QueryClient;
use Phpactor\Indexer\Model\SearchClient;
use Phpactor\Indexer\Model\Indexer;
use Phpactor\TextDocument\FilesystemTextDocumentLocator;
use Phpactor\TextDocument\TextDocumentLocator;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Reflector;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

class IndexerExtension implements Extension
{
    public const PARAM_INDEX_PATH = 'indexer.index_path';
    public const PARAM_INDEXER_POLL_TIME = 'indexer.poll_time';
    public const PARAM_ENABLED_WATCHERS = 'indexer.enabled_watchers';
    public const PARAM_INCLUDE_PATTERNS = 'indexer.include_patterns';
    public const PARAM_EXCLUDE_PATTERNS = 'indexer.exclude_patterns';
    public const PARAM_INDEXER_BUFFER_TIME = 'indexer.buffer_time';
    public const PARAM_INDEXER_FOLLOW_SYMLINKS = 'indexer.follow_symlinks';
    public const PARAM_INDEXER_MAX_FILESIZE_TO_INDEX = 'indexer.max_filesize_to_index';
    public const PARAM_REFERENCES_DEEP_REFERENCES = 'indexer.reference_finder.deep';
    public const PARAM_IMPLEMENTATIONS_DEEP_REFERENCES = 'indexer.implementation_finder.deep';
    public const PARAM_STUB_PATHS = 'indexer.stub_paths';
    public const PARAM_SUPPORTED_EXTENSIONS = 'indexer.supported_extensions';
    public const TAG_WATCHER = 'indexer.watcher';
    private const SERVICE_INDEXER_EXCLUDE_PATTERNS = 'indexer.exclude_patterns';
    private const SERVICE_INDEXER_INCLUDE_PATTERNS = 'indexer.include_patterns';
    private const PARAM_PROJECT_ROOT = 'indexer.project_root';
    private const PARAM_SEARCH_INCLUDE_PATTERNS = 'indexer.search_include_patterns';


    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_ENABLED_WATCHERS => ['inotify', 'watchman', 'find', 'php'],
            self::PARAM_INDEX_PATH => '%cache%/index/%project_id%',
            self::PARAM_INCLUDE_PATTERNS => [
                '/**/*.php',
                '/**/*.phar',
            ],
            self::PARAM_EXCLUDE_PATTERNS => [
                '/vendor/**/Tests/**/*',
                '/vendor/**/tests/**/*',
                '/vendor/composer/**/*',
                // rector frequently breaks phpunit testcase reflection so just
                // ignore the stubs by default
                '/vendor/rector/rector/stubs-rector'
            ],
            self::PARAM_STUB_PATHS => [],
            self::PARAM_INDEXER_POLL_TIME => 5000,
            self::PARAM_INDEXER_BUFFER_TIME => 500,
            self::PARAM_INDEXER_FOLLOW_SYMLINKS => false,
            self::PARAM_INDEXER_MAX_FILESIZE_TO_INDEX => 1_000_000,
            self::PARAM_PROJECT_ROOT => '%project_root%',
            self::PARAM_REFERENCES_DEEP_REFERENCES => true,
            self::PARAM_IMPLEMENTATIONS_DEEP_REFERENCES => true,
            self::PARAM_SUPPORTED_EXTENSIONS => ['php', 'phar'],
            self::PARAM_SEARCH_INCLUDE_PATTERNS => [],
        ]);
        $schema->setDescriptions([
            self::PARAM_ENABLED_WATCHERS => 'List of allowed watchers. The first watcher that supports the current system will be used',
            self::PARAM_INDEX_PATH => 'Path where the index should be saved',
            self::PARAM_STUB_PATHS => 'Paths to external folders to index. They will be indexed only once, if you want to take any changes into account you will have to reindex your project manually.',
            self::PARAM_INCLUDE_PATTERNS => 'Glob patterns to include while indexing',
            self::PARAM_EXCLUDE_PATTERNS => 'Glob patterns to exclude while indexing',
            self::PARAM_INDEXER_POLL_TIME => 'For polling indexers only: the time, in milliseconds, between polls (e.g. filesystem scans)',
            self::PARAM_INDEXER_BUFFER_TIME => 'For real-time indexers only: the time, in milliseconds, to buffer the results',
            self::PARAM_INDEXER_FOLLOW_SYMLINKS => 'To allow indexer to follow symlinks',
            self::PARAM_INDEXER_MAX_FILESIZE_TO_INDEX => 'Files larger than this will not be indexed. (Size in bytes)',
            self::PARAM_PROJECT_ROOT => 'The root path to use for scanning the index',
            self::PARAM_REFERENCES_DEEP_REFERENCES => 'Recurse over class implementations to resolve all references',
            self::PARAM_IMPLEMENTATIONS_DEEP_REFERENCES => 'Recurse over class implementations to resolve all class implementations (not just the classes directly implementing the subject)',
            self::PARAM_SUPPORTED_EXTENSIONS => 'File extensions (e.g. `php`) for files that should be indexed',
            self::PARAM_SEARCH_INCLUDE_PATTERNS => 'When searching the index exclude records whose fully qualified names match any of these regex patterns (use to exclude suggestions from search results). Namespace separators must be escaped as `\\\\\\\\` for example `^Foo\\\\\\\\` to include all namespaces whose first segment is `Foo`',
        ]);
        $schema->setTypes([
            self::PARAM_ENABLED_WATCHERS => 'array',
            self::PARAM_INDEX_PATH => 'string',
            self::PARAM_INCLUDE_PATTERNS => 'array',
            self::PARAM_EXCLUDE_PATTERNS => 'array',
            self::PARAM_STUB_PATHS => 'array',
            self::PARAM_INDEXER_POLL_TIME => 'integer',
            self::PARAM_INDEXER_BUFFER_TIME => 'integer',
            self::PARAM_INDEXER_FOLLOW_SYMLINKS => 'boolean',
            self::PARAM_INDEXER_MAX_FILESIZE_TO_INDEX => 'integer',
            self::PARAM_PROJECT_ROOT => 'string',
            self::PARAM_REFERENCES_DEEP_REFERENCES => 'boolean',
            self::PARAM_IMPLEMENTATIONS_DEEP_REFERENCES => 'boolean',
            self::PARAM_SUPPORTED_EXTENSIONS => 'array',
            self::PARAM_SEARCH_INCLUDE_PATTERNS => 'array',
        ]);
    }


    public function load(ContainerBuilder $container): void
    {
        $this->registerCommands($container);
        $this->registerModel($container);
        $this->registerWorseAdapters($container);
        $this->registerRpc($container);
        $this->registerReferenceFinderAdapters($container);
        $this->registerWatcher($container);
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
        $container->register(IndexerConstantSourceLocator::class, function (Container $container) {
            return new IndexerConstantSourceLocator($container->get(IndexAccess::class));
        }, [
            WorseReflectionExtension::TAG_SOURCE_LOCATOR => [
                'priority' => 100,
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

        $container->register(IndexCleanCommand::class, function (Container $container) {
            $indexPath = $container->get(
                FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER
            )->resolve($container->parameter(self::PARAM_INDEX_PATH)->string());

            $indexPath = dirname($indexPath);
            return new IndexCleanCommand(new PhpIndexerLister($indexPath), new Filesystem());
        }, [ ConsoleExtension::TAG_COMMAND => ['name' => 'index:clean']]);

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
            $resolver = $container->expect(
                FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER,
                PathResolver::class
            );
            $indexPath = $resolver->resolve($container->parameter(self::PARAM_INDEX_PATH)->string());

            /** @var array<string> $stubPaths */
            $stubPaths = $container->parameter(self::PARAM_STUB_PATHS)->value();
            $stubPaths = array_map(fn (string $path): string => $resolver->resolve($path), $stubPaths);

            return IndexAgentBuilder::create($indexPath, $this->projectRoot($container))
                /** @phpstan-ignore-next-line */
                ->setExcludePatterns($container->get(self::SERVICE_INDEXER_EXCLUDE_PATTERNS))
                /** @phpstan-ignore-next-line */
                ->setIncludePatterns($container->get(self::SERVICE_INDEXER_INCLUDE_PATTERNS))
                ->setSearchIncludePatterns($container->parameter(self::PARAM_SEARCH_INCLUDE_PATTERNS)->listOfString())
                /** @phpstan-ignore-next-line */
                ->setSupportedExtensions($container->parameter(self::PARAM_SUPPORTED_EXTENSIONS)->value())
                ->setFollowSymlinks($container->parameter(self::PARAM_INDEXER_FOLLOW_SYMLINKS)->bool())
                ->setMaxFileSizeToIndex($container->parameter(self::PARAM_INDEXER_MAX_FILESIZE_TO_INDEX)->int())
                ->setStubPaths($stubPaths);
        });

        $container->register(Indexer::class, function (Container $container) {
            return $container->get(IndexAgent::class)->indexer();
        });

        $container->register(self::SERVICE_INDEXER_EXCLUDE_PATTERNS, function (Container $container) {
            $projectRoot = $this->projectRoot($container);
            return array_map(function (string $pattern) use ($projectRoot) {
                return Path::join($projectRoot, $pattern);
            }, $container->getParameter(self::PARAM_EXCLUDE_PATTERNS));
        });

        $container->register(self::SERVICE_INDEXER_INCLUDE_PATTERNS, function (Container $container) {
            $projectRoot = $container->getParameter(FilePathResolverExtension::PARAM_PROJECT_ROOT);

            return array_map(function (string $pattern) use ($projectRoot) {
                return Path::join($projectRoot, $pattern);
            }, $container->getParameter(self::PARAM_INCLUDE_PATTERNS));
        });

        $container->register(WorseRecordReferenceEnhancer::class, function (Container $container) {
            return new WorseRecordReferenceEnhancer(
                $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class),
                $this->logger($container),
                $container->has(TextDocumentLocator::class) ? $container->get(TextDocumentLocator::class) : new FilesystemTextDocumentLocator(),
            );
        });
    }

    private function registerReferenceFinderAdapters(ContainerBuilder $container): void
    {
        $container->register(IndexedImplementationFinder::class, function (Container $container) {
            return new IndexedImplementationFinder(
                $container->get(QueryClient::class),
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->parameter(self::PARAM_IMPLEMENTATIONS_DEEP_REFERENCES)->bool()
            );
        }, [ ReferenceFinderExtension::TAG_IMPLEMENTATION_FINDER => []]);

        $container->register(IndexedReferenceFinder::class, function (Container $container) {
            return new IndexedReferenceFinder(
                $container->get(QueryClient::class),
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                new ContainerTypeResolver($container->get(WorseReflectionExtension::SERVICE_REFLECTOR)),
                $container->parameter(self::PARAM_REFERENCES_DEEP_REFERENCES)->bool()
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

            foreach ($container->getServiceIdsForTag(self::TAG_WATCHER) as $serviceId => $attrs) {
                if (!isset($attrs['name'])) {
                    throw new RuntimeException(sprintf(
                        'Watcher "%s" must provide the `name` attribute',
                        $serviceId
                    ));
                }

                $watchers[$attrs['name']] = $serviceId;
            }

            /** @var list<string> $enabledWatchers */
            $enabledWatchers = $container->getParameter(self::PARAM_ENABLED_WATCHERS);
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
                new FallbackWatcher($watchers, $this->logger($container)),
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
            ], $container->parameter(self::PARAM_INDEXER_POLL_TIME)->int());
        });

        // register watchers - order of registration currently determines
        // priority

        $container->register(WatchmanWatcher::class, function (Container $container) {
            return new BufferedWatcher(new WatchmanWatcher(
                $container->get(WatcherConfig::class),
                $this->logger($container)
            ), $container->parameter(self::PARAM_INDEXER_BUFFER_TIME)->int());
        }, [
            self::TAG_WATCHER => [
                'name' => 'watchman',
            ]
        ]);

        $container->register(InotifyWatcher::class, function (Container $container) {
            return new BufferedWatcher(new InotifyWatcher(
                $container->get(WatcherConfig::class),
                $this->logger($container)
            ), $container->parameter(self::PARAM_INDEXER_BUFFER_TIME)->int());
        }, [
            self::TAG_WATCHER => [
                'name' => 'inotify',
            ]
        ]);

        $container->register(FsWatchWatcher::class, function (Container $container) {
            return new FsWatchWatcher(
                $container->get(WatcherConfig::class),
                $this->logger($container)
            );
        }, [
            self::TAG_WATCHER => [
                'name' => 'fswatch',
            ]
        ]);

        $container->register(FindWatcher::class, function (Container $container) {
            return new FindWatcher(
                $container->get(WatcherConfig::class),
                $this->logger($container)
            );
        }, [
            self::TAG_WATCHER => [
                'name' => 'find',
            ]
        ]);

        $container->register(PhpPollWatcher::class, function (Container $container) {
            return new PhpPollWatcher(
                $container->get(WatcherConfig::class),
                $this->logger($container)
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
        )->resolve($container->parameter(self::PARAM_PROJECT_ROOT)->string());
    }

    private function logger(Container $container): LoggerInterface
    {
        return LoggingExtension::channelLogger($container, 'indexer');
    }
}
