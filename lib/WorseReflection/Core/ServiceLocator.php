<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser\CachedParserFactory;
use Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser\DocblockParserFactory;
use Phpactor\WorseReflection\Core\Cache\NullCache;
use Phpactor\WorseReflection\Core\Cache\StaticCache;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Phpactor\WorseReflection\Core\Inference\Walker\DiagnosticsWalker;
use Phpactor\WorseReflection\Core\Inference\Walker\PassThroughWalker;
use Phpactor\WorseReflection\Core\Inference\Walker\FunctionLikeWalker;
use Phpactor\WorseReflection\Core\Inference\Walker\IncludeWalker;
use Phpactor\WorseReflection\Core\Inference\Walker\VariableWalker;
use Phpactor\WorseReflection\Core\Inference\NodeToTypeConverter;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
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
    
    private DocBlockFactory $docblockFactory;

    /**
     * @var array<int,ReflectionMemberProvider>
     */
    private array $methodProviders;

    private Cache $cache;

    /**
     * @var DiagnosticProvider[]
     */
    private array $diagnosticProviders;

    /**
     * @var Walker[]
     */
    private array $frameWalkers;

    private NodeToTypeConverter $nameResolver;

    /**
     * @param Walker[] $frameWalkers
     * @param ReflectionMemberProvider[] $methodProviders
     * @param DiagnosticProvider[] $diagnosticProviders
     */
    public function __construct(
        SourceCodeLocator $sourceLocator,
        LoggerInterface $logger,
        SourceCodeReflectorFactory $reflectorFactory,
        array $frameWalkers,
        array $methodProviders,
        array $diagnosticProviders,
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
        if (!$cache instanceof NullCache) {
            $this->docblockFactory = new CachedParserFactory($this->docblockFactory, $cache);
        }
        $this->logger = $logger;

        $this->nameResolver = new NodeToTypeConverter($this->reflector, $this->logger);

        $this->methodProviders = $methodProviders;
        $this->diagnosticProviders = $diagnosticProviders;
        $this->cache = $cache;
        $this->frameWalkers = $frameWalkers;
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

    public function symbolContextResolver(): NodeContextResolver
    {
        return new NodeContextResolver(
            $this->reflector,
            $this->logger,
            new StaticCache(),
            (new DefaultResolverFactory(
                $this->reflector,
                $this->nameResolver
            ))->createResolvers(),
        );
    }

    public function frameBuilder(): FrameResolver
    {
        return FrameResolver::create(
            $this->symbolContextResolver(),
            array_merge([
                new FunctionLikeWalker(),
                new PassThroughWalker(),
                new VariableWalker($this->docblockFactory),
                new IncludeWalker($this->logger),
            ], $this->frameWalkers),
        );
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

    public function newDiagnosticsWalker(): DiagnosticsWalker
    {
        return new DiagnosticsWalker($this->diagnosticProviders);
    }
}
