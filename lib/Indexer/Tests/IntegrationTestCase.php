<?php

namespace Phpactor\Indexer\Tests;

use PHPUnit\Framework\TestCase;
use Phpactor\Indexer\Adapter\Tolerant\TolerantIndexBuilder;
use Phpactor\Indexer\Model\IndexBuilder;
use Phpactor\TextDocument\FilesystemTextDocumentLocator;
use Phpactor\Extension\ReferenceFinder\ReferenceFinderExtension;
use Phpactor\Extension\ComposerAutoloader\ComposerAutoloaderExtension;
use Phpactor\Extension\Rpc\RpcExtension;
use Phpactor\Extension\ClassToFile\ClassToFileExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Indexer\Adapter\Worse\WorseRecordReferenceEnhancer;
use Phpactor\Indexer\Extension\IndexerExtension;
use Phpactor\Container\Container;
use Phpactor\Indexer\IndexAgentBuilder;
use Phpactor\Indexer\Model\QueryClient;
use Phpactor\Indexer\Model\TestIndexAgent;
use Phpactor\WorseReflection\Reflector;
use Phpactor\TestUtils\Workspace;
use Phpactor\Indexer\Model\Index;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StubSourceLocator;
use Phpactor\WorseReflection\ReflectorBuilder;
use Symfony\Component\Process\Process;

class IntegrationTestCase extends TestCase
{
    protected function workspace(): Workspace
    {
        return Workspace::create(__DIR__ . '/Workspace');
    }

    protected function initProject(): void
    {
        $this->workspace()->loadManifest((string)file_get_contents(__DIR__ . '/Adapter/Manifest/buildIndex.php.test'));
        $process = new Process([
            'composer', 'install'
        ], $this->workspace()->path('/'));
        $process->mustRun();
    }

    protected function indexAgent(): TestIndexAgent
    {
        return $this->indexAgentBuilder()->buildTestAgent();
    }

    protected function indexAgentBuilder(
        string $path = 'project',
        ?IndexBuilder $indexBuilder = null,
    ): IndexAgentBuilder {
        return IndexAgentBuilder::create(
            $this->workspace()->path('repo'),
            $this->workspace()->path($path),
            $indexBuilder ?? TolerantIndexBuilder::create(),
        )->setReferenceEnhancer(
            new WorseRecordReferenceEnhancer(
                $this->createReflector(),
                $this->createLogger(),
                new FilesystemTextDocumentLocator(),
            )
        );
    }

    protected function buildIndex(?Index $index = null): Index
    {
        $agent = $this->indexAgent();
        $agent->indexer()->getJob()->run();

        return $agent->index();
    }

    protected function createReflector(): Reflector
    {
        return ReflectorBuilder::create()->addLocator(
            new StubSourceLocator(
                ReflectorBuilder::create()->build(),
                $this->workspace()->path('/'),
                $this->workspace()->path('/')
            )
        )->build();
    }

    protected function indexQuery(Index $index): QueryClient
    {
        return new QueryClient(
            $index,
            new WorseRecordReferenceEnhancer(
                $this->createReflector(),
                $this->createLogger(),
                new FilesystemTextDocumentLocator(),
            )
        );
    }
    /**
     * @param array<int,mixed> $config
     */
    protected function container(array $config = []): Container
    {
        $key = serialize($config);
        static $container = [];

        if (isset($container[$key])) {
            return $container[$key];
        }

        $container[$key] = PhpactorContainer::fromExtensions(
            [
            ConsoleExtension::class,
            IndexerExtension::class,
            FilePathResolverExtension::class,
            LoggingExtension::class,
            SourceCodeFilesystemExtension::class,
            WorseReflectionExtension::class,
            ClassToFileExtension::class,
            RpcExtension::class,
            ComposerAutoloaderExtension::class,
            ReferenceFinderExtension::class,
        ],
            array_merge([
            FilePathResolverExtension::PARAM_APPLICATION_ROOT => __DIR__ . '/../',
            FilePathResolverExtension::PARAM_PROJECT_ROOT => $this->workspace()->path(),
            IndexerExtension::PARAM_INDEX_PATH => $this->workspace()->path('/cache'),
            LoggingExtension::PARAM_ENABLED=> true,
            LoggingExtension::PARAM_PATH=> 'php://stderr',
            WorseReflectionExtension::PARAM_ENABLE_CACHE=> false,
            WorseReflectionExtension::PARAM_STUB_DIR => $this->workspace()->path(),
        ], $config)
        );

        return $container[$key];
    }

    private function createLogger(): LoggerInterface
    {
        return new class() extends AbstractLogger {
            public function log($level, $message, array $context = []): void
            {
                fwrite(STDOUT, sprintf("[%s] %s\n", $level, $message));
            }
        };
    }
}
