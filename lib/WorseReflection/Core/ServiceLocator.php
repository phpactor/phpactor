<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser\DocblockParserFactory;
use Phpactor\WorseReflection\Core\Cache\NullCache;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder\AssertFrameWalker;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder\AssignmentWalker;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder\CatchWalker;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder\ForeachWalker;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder\FunctionLikeWalker;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder\IncludeWalker;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder\InstanceOfWalker;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder\VariableWalker;
use Phpactor\WorseReflection\Core\Inference\FrameWalker;
use Phpactor\WorseReflection\Core\Inference\FullyQualifiedNameResolver;
use Phpactor\WorseReflection\Core\Inference\SymbolContextResolver;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder;
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
    
    private FrameBuilder $frameBuilder;
    
    private SymbolContextResolver $symbolContextResolver;
    
    private DocBlockFactory $docblockFactory;

    /**
     * @var array<int,ReflectionMemberProvider>
     */
    private array $methodProviders;

    /**
     * @param list<FrameWalker> $frameWalkers
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

        $nameResolver = new FullyQualifiedNameResolver($this->reflector, $this->logger);
        $this->symbolContextResolver = new SymbolContextResolver(
            $this->reflector,
            $this->logger,
            $cache,
            $nameResolver,
        );

        $this->frameBuilder = FrameBuilder::create(
            $this->symbolContextResolver,
            $cache,
            array_merge([
                new AssertFrameWalker($this->reflector),
                new FunctionLikeWalker(),
                new VariableWalker($this->docblockFactory, $nameResolver),
                new AssignmentWalker($this->logger),
                new CatchWalker(),
                new ForeachWalker(),
                new InstanceOfWalker($this->reflector),
                new IncludeWalker($logger),
            ], $frameWalkers)
        );
        $this->methodProviders = $methodProviders;
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
    public function symbolContextResolver(): SymbolContextResolver
    {
        return $this->symbolContextResolver;
    }

    /**
     * TODO: This is TolerantParser specific.
     */
    public function frameBuilder(): FrameBuilder
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
}
