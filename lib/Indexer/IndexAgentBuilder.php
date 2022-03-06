<?php

namespace Phpactor\Indexer;

use Phpactor\Filesystem\Domain\FilePath;
use Phpactor\Filesystem\Adapter\Simple\SimpleFileListProvider;
use Phpactor\Filesystem\Adapter\Simple\SimpleFilesystem;
use Phpactor\Indexer\Adapter\Filesystem\FilesystemFileListProvider;
use Phpactor\Indexer\Adapter\Php\FileSearchIndex;
use Phpactor\Indexer\Adapter\Php\Serialized\FileRepository;
use Phpactor\Indexer\Adapter\Php\Serialized\SerializedIndex;
use Phpactor\Indexer\Adapter\Tolerant\TolerantIndexBuilder;
use Phpactor\Indexer\Adapter\Tolerant\TolerantIndexer;
use Phpactor\Indexer\Model\FileListProvider;
use Phpactor\Indexer\Model\FileListProvider\ChainFileListProvider;
use Phpactor\Indexer\Model\FileListProvider\DirtyFileListProvider;
use Phpactor\Indexer\Model\Index;
use Phpactor\Indexer\Model\IndexAccess;
use Phpactor\Indexer\Model\Index\SearchAwareIndex;
use Phpactor\Indexer\Model\RealIndexAgent;
use Phpactor\Indexer\Model\IndexBuilder;
use Phpactor\Indexer\Model\QueryClient;
use Phpactor\Indexer\Model\Indexer;
use Phpactor\Indexer\Model\RecordReferenceEnhancer;
use Phpactor\Indexer\Model\RecordReferenceEnhancer\NullRecordReferenceEnhancer;
use Phpactor\Indexer\Model\RecordSerializer;
use Phpactor\Indexer\Model\RecordSerializer\PhpSerializer;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\Indexer\Model\Record\ConstantRecord;
use Phpactor\Indexer\Model\Record\FunctionRecord;
use Phpactor\Indexer\Model\SearchClient\HydratingSearchClient;
use Phpactor\Indexer\Model\SearchIndex;
use Phpactor\Indexer\Model\SearchIndex\FilteredSearchIndex;
use Phpactor\Indexer\Model\SearchIndex\ValidatingSearchIndex;
use Phpactor\Indexer\Model\TestIndexAgent;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class IndexAgentBuilder
{
    /**
     * @var string
     */
    private $indexRoot;

    /**
     * @var RecordReferenceEnhancer
     */
    private $enhancer;

    /**
     * @var array<string>
     */
    private $includePatterns = [
        '/**/*.php',
    ];

    /**
     * @var array<string>
     */
    private $stubPaths = [];

    /**
     * @var array<string>
     */
    private $excludePatterns = [
    ];

    /**
     * @var string
     */
    private $projectRoot;

    /**
     * @var array<TolerantIndexer>|null
     */
    private $indexers = null;

    /**
     * @var LoggerInterface
     */
    private $logger;

    private function __construct(string $indexRoot, string $projectRoot)
    {
        $this->indexRoot = $indexRoot;
        $this->enhancer = new NullRecordReferenceEnhancer();
        $this->logger = new NullLogger();
        $this->projectRoot = $projectRoot;
    }

    public static function create(string $indexRootPath, string $projectRoot): self
    {
        return new self($indexRootPath, $projectRoot);
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function addStubPath(string $path): self
    {
        $this->stubPaths[] = $path;

        return $this;
    }

    public function setReferenceEnhancer(RecordReferenceEnhancer $enhancer): self
    {
        $this->enhancer = $enhancer;

        return $this;
    }

    public function buildAgent(): IndexAgent
    {
        return $this->buildTestAgent();
    }

    public function buildTestAgent(): TestIndexAgent
    {
        $index = $this->buildIndex();
        $search = $this->buildSearch($index);
        $index = new SearchAwareIndex($index, $search);
        $query = $this->buildQuery($index);
        $builder = $this->buildBuilder($index);
        $indexer = $this->buildIndexer($builder, $index);
        $search = new HydratingSearchClient($index, $search);

        return new RealIndexAgent($index, $query, $search, $indexer);
    }

    /**
     * @param array<TolerantIndexer> $indexers
     */
    public function setIndexers(array $indexers): self
    {
        $this->indexers = $indexers;

        return $this;
    }

    /**
     * @param array<string> $excludePatterns
     */
    public function setExcludePatterns(array $excludePatterns): self
    {
        $this->excludePatterns = $excludePatterns;

        return $this;
    }

    /**
     * @param array<string> $includePatterns
     */
    public function setIncludePatterns(array $includePatterns): self
    {
        $this->includePatterns = $includePatterns;

        return $this;
    }

    /**
     * @param array<string> $stubPaths
     */
    public function setStubPaths(array $stubPaths): self
    {
        $this->stubPaths = $stubPaths;

        return $this;
    }

    private function buildIndex(): Index
    {
        $repository = new FileRepository(
            $this->indexRoot,
            $this->buildRecordSerializer(),
            $this->logger
        );

        return new SerializedIndex($repository);
    }

    private function buildQuery(Index $index): QueryClient
    {
        return new QueryClient(
            $index,
            $this->enhancer
        );
    }

    private function buildSearch(IndexAccess $index): SearchIndex
    {
        $search = new FileSearchIndex($this->indexRoot . '/search');
        $search = new ValidatingSearchIndex($search, $index, $this->logger);
        $search = new FilteredSearchIndex($search, [
            ClassRecord::RECORD_TYPE,
            FunctionRecord::RECORD_TYPE,
            ConstantRecord::RECORD_TYPE,
        ]);

        return $search;
    }

    private function buildBuilder(Index $index): IndexBuilder
    {
        if (null !== $this->indexers) {
            return new TolerantIndexBuilder($index, $this->indexers, $this->logger);
        }
        return TolerantIndexBuilder::create($index);
    }

    private function buildIndexer(IndexBuilder $builder, Index $index): Indexer
    {
        return new Indexer(
            $builder,
            $index,
            $this->buildFileListProvider(),
            $this->buildDirtyTracker()
        );
    }

    private function buildFileListProvider(): FileListProvider
    {
        return new ChainFileListProvider(...$this->buildFileListProviders());
    }

    private function buildFilesystem(string $root): SimpleFilesystem
    {
        return new SimpleFilesystem(
            $this->indexRoot,
            new SimpleFileListProvider(FilePath::fromString($root))
        );
    }

    private function buildRecordSerializer(): RecordSerializer
    {
        return new PhpSerializer();
    }

    /**
     * @return array<FileListProvider>
     */
    private function buildFileListProviders(): array
    {
        $providers = [
            new FilesystemFileListProvider(
                $this->buildFilesystem($this->projectRoot),
                $this->includePatterns,
                $this->excludePatterns
            )
        ];

        foreach ($this->stubPaths as $stubPath) {
            $providers[] = new FilesystemFileListProvider(
                $this->buildFilesystem($stubPath)
            );
        }

        $providers[] = $this->buildDirtyTracker();

        return $providers;
    }

    private function buildDirtyTracker(): DirtyFileListProvider
    {
        return new DirtyFileListProvider($this->indexRoot . '/dirty');
    }
}
