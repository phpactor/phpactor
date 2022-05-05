<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser\DocblockParserFactory;
use Phpactor\WorseReflection\Core\Cache\NullCache;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Phpactor\WorseReflection\Core\Inference\Walker\AssertFrameWalker;
use Phpactor\WorseReflection\Core\Inference\Walker\AssignmentWalker;
use Phpactor\WorseReflection\Core\Inference\Walker\BinaryExpressionWalker;
use Phpactor\WorseReflection\Core\Inference\Walker\CatchWalker;
use Phpactor\WorseReflection\Core\Inference\Walker\ForeachWalker;
use Phpactor\WorseReflection\Core\Inference\Walker\FunctionLikeWalker;
use Phpactor\WorseReflection\Core\Inference\Walker\IncludeWalker;
use Phpactor\WorseReflection\Core\Inference\Walker\IfStatementWalker;
use Phpactor\WorseReflection\Core\Inference\Walker\ReturnTypeWalker;
use Phpactor\WorseReflection\Core\Inference\Walker\VariableWalker;
use Phpactor\WorseReflection\Core\Inference\NodeToTypeConverter;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\Inference\Walker\YieldWalker;
use Phpactor\WorseReflection\Core\Virtual\ReflectionMemberProvider;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\Reflector\CoreReflector;
use Phpactor\WorseReflection\Core\Reflector\CompositeReflector;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector\MemonizedReflector;
use Phpactor\WorseReflection\Core\Reflector\SourceCode\ContextualSourceCodeReflector;
use Phpactor\WorseReflection\Core\SourceCodeLocator\ChainSourceLocator;
use Phpactor\WorseReflection\Core\SourceCodeLocator\TemporarySourceLocator;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockFactory;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflectorFactory;
use Psr\Log\LoggerInterface;

class ServiceLocator
{
    private SourceCodeLocator $sourceLocator;
    
    private LoggerInterface $logger;
    
    private Reflector $reflector;
    
    private FrameResolver $frameBuilder;
    
    private NodeContextResolver $symbolContextResolver;
    
    private DocBlockFactory $docblockFactory;

    /**
     * @var array<int,ReflectionMemberProvider>
     */
    private array $methodProviders;

    private Cache $cache;

    /**
     * @param list<Walker> $frameWalkers
     * @param list<ReflectionMemberProvider> $methodProviders
     */
    public function __construct(
        SourceCodeLocator $sourceLocator,
        LoggerInterface $logger,
        SourceCodeReflectorFactory $reflectorFactory,
        array $frameWalkers,
        array $methodProviders,
        Cache $cache,
        bool $enableContextualLocation = false
    ) {
        $sourceReflector = $reflectorFactory->create($this);

        if ($enableContextualLocation) {
            $temporarySourceLocator = new TemporarySourceLocator($sourceReflector);
            $sourceLocator = new ChainSourceLocator([
                $temporarySourceLocator,
                $sourceLocator,
            ], $logger);
            $sourceReflector = new ContextualSourceCodeReflector($sourceReflector, $temporarySourceLocator);
        }

        $coreReflector = new CoreReflector($sourceReflector, $sourceLocator);

        if (!$cache instanceof NullCache) {
            $coreReflector = new MemonizedReflector($coreReflector, $coreReflector, $cache);
        }

        $this->reflector = new CompositeReflector(
            $coreReflector,
            $sourceReflector,
            $coreReflector
        );

        $this->sourceLocator = $sourceLocator;
        $this->docblockFactory = new DocblockParserFactory($this->reflector);
        $this->logger = $logger;

        $nameResolver = new NodeToTypeConverter($this->reflector, $this->logger);
        $this->symbolContextResolver = new NodeContextResolver(
            $this->reflector,
            $this->logger,
            $cache,
            (new DefaultResolverFactory(
                $this->reflector,
                $nameResolver
            ))->createResolvers(),
        );


        $this->frameBuilder = FrameResolver::create(
            $this->symbolContextResolver,
            $cache,
            array_merge([
                new AssertFrameWalker(),
                new ReturnTypeWalker(),
                new FunctionLikeWalker(),
                new VariableWalker($this->docblockFactory),
                new AssignmentWalker($this->logger),
                new CatchWalker(),
                new ForeachWalker(),
                new IfStatementWalker(),
                new IncludeWalker($logger),
                new BinaryExpressionWalker(),
                new YieldWalker(),
            ], $frameWalkers)
        );
        $this->methodProviders = $methodProviders;
        $this->cache = $cache;
    }

    public function reflector(): Reflector
    {
        return $this->reflector;
    }

    public function logger(): LoggerInterface
    {
        return $this->logger;
    }

    public function sourceLocator(): SourceCodeLocator
    {
        return $this->sourceLocator;
    }

    public function docblockFactory(): DocBlockFactory
    {
        return $this->docblockFactory;
    }

    /**
     * TODO: This is TolerantParser specific.
     */
    public function symbolContextResolver(): NodeContextResolver
    {
        return $this->symbolContextResolver;
    }

    /**
     * TODO: This is TolerantParser specific.
     */
    public function frameBuilder(): FrameResolver
    {
        return $this->frameBuilder;
    }

    /**
     * @return list<ReflectionMemberProvider>
     */
    public function methodProviders(): array
    {
        return $this->methodProviders;
    }

    public function cache(): Cache
    {
        return $this->cache;
    }
}
