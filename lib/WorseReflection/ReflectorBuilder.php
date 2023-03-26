<?php

namespace Phpactor\WorseReflection;

use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Cache;
use Phpactor\WorseReflection\Core\Cache\NullCache;
use Phpactor\WorseReflection\Core\Cache\TtlCache;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Inference\Resolver\MemberAccess\MemberContextResolver;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Phpactor\WorseReflection\Bridge\PsrLog\ArrayLogger;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\SourceCodeLocator\ChainSourceLocator;
use Phpactor\WorseReflection\Core\SourceCodeLocator\InternalLocator;
use Phpactor\WorseReflection\Core\SourceCodeLocator\NullSourceLocator;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StringSourceLocator;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflector\TolerantFactory;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflectorFactory;
use Phpactor\WorseReflection\Core\Virtual\ReflectionMemberProvider;
use Psr\Log\LoggerInterface;

final class ReflectorBuilder
{
    private ?LoggerInterface $logger = null;

    /**
     * @var SourceCodeLocator[]
     */
    private array $locators = [];

    private bool $contextualSourceLocation = false;

    private bool $enableCache = false;

    private bool $enableContextualSourceLocation = false;

    /**
     * @var SourceCodeReflectorFactory
     */
    private ?SourceCodeReflectorFactory $sourceReflectorFactory = null;

    /**
     * @var Walker[]
     */
    private array $framewalkers = [];

    /**
     * @var ReflectionMemberProvider[]
     */
    private array $memberProviders = [];

    private float $cacheLifetime = 5.0;

    private ?Cache $cache = null;

    /**
     * @var DiagnosticProvider[]
     */
    private array $diagnosticProviders = [];

    /**
     * @var MemberContextResolver[]
     */
    private array $memberContextResolvers = [];

    /**
     * Create a new instance of the builder
     */
    public static function create(): ReflectorBuilder
    {
        return new self();
    }

    public function withSourceReflectorFactory(SourceCodeReflectorFactory $sourceReflectorFactory): ReflectorBuilder
    {
        $this->sourceReflectorFactory = $sourceReflectorFactory;
        return $this;
    }

    /**
     * Replace the logger implementation.
     */
    public function withLogger(LoggerInterface $logger): ReflectorBuilder
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Add a source locator
     */
    public function addLocator(SourceCodeLocator $locator, int $priority = 0): ReflectorBuilder
    {
        $this->locators[] = [$priority, $locator];

        return $this;
    }

    /**
     * Add some source code
     */
    public function addSource(TextDocument|string $code): ReflectorBuilder
    {
        $source = TextDocument::fromUnknown($code);

        $this->addLocator(new StringSourceLocator($source));

        return $this;
    }

    public function addFrameWalker(Walker $frameWalker): ReflectorBuilder
    {
        $this->framewalkers[] = $frameWalker;
        return $this;
    }

    public function addMemberProvider(ReflectionMemberProvider $provider): ReflectorBuilder
    {
        $this->memberProviders[] = $provider;
        return $this;
    }

    public function addDiagnosticProvider(DiagnosticProvider $provider): self
    {
        $this->diagnosticProviders[] = $provider;
        return $this;
    }

    /**
     * Build the reflector
     */
    public function build(): Reflector
    {
        $this->addLocator(InternalLocator::forInternalStubs(), 255);
        return (new ServiceLocator(
            $this->buildLocator(),
            $this->buildLogger(),
            $this->buildReflectorFactory(),
            $this->framewalkers,
            $this->memberProviders,
            $this->diagnosticProviders,
            $this->memberContextResolvers,
            $this->buildCache(),
            $this->enableContextualSourceLocation
        ))->reflector();
    }

    /**
     * Enable contextual source location.
     *
     * Enable WR to locate classes from any source code passed
     * to the SourceReflector (this is to enable property / class
     * reflection on the current class.
     *
     * WARNING: This makes the reflector stateful - any source code
     *          passed to source reflector methods will be retained
     *          for the duration of the process.
     */
    public function enableContextualSourceLocation(): ReflectorBuilder
    {
        $this->enableContextualSourceLocation = true;

        return $this;
    }

    /**
     * Enable class reflection cache.
     *
     * Wraps the ClassReflector in a memonizing cache.
     */
    public function enableCache(): ReflectorBuilder
    {
        $this->enableCache = true;

        return $this;
    }

    public function withCache(Cache $cache): ReflectorBuilder
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Set the cache lifetime in seconds (floats accepted)
     */
    public function cacheLifetime(float $lifetime): ReflectorBuilder
    {
        $this->cacheLifetime = $lifetime;

        return $this;
    }

    public function addMemberContextResolver(MemberContextResolver $memberContextResolver): self
    {
        $this->memberContextResolvers[] = $memberContextResolver;
        return $this;
    }

    private function buildLocator(): SourceCodeLocator
    {
        $locators = $this->locators;
        usort($locators, function ($locator1, $locator2) {
            [ $priority1 ] = $locator1;
            [ $priority2 ] = $locator2;
            return $priority2 <=> $priority1;
        });

        $locators = array_map(function (array $locator) {
            return $locator[1];
        }, $locators);

        if (empty($locators)) {
            return new NullSourceLocator();
        }

        if (count($locators) > 1) {
            return new ChainSourceLocator($locators, $this->logger);
        }

        return reset($locators);
    }

    private function buildLogger(): LoggerInterface
    {
        return $this->logger ?: new ArrayLogger();
    }

    private function buildReflectorFactory(): SourceCodeReflectorFactory
    {
        return $this->sourceReflectorFactory ?: new TolerantFactory();
    }

    private function buildCache(): Cache
    {
        if ($this->enableCache) {
            return $this->cache ?: new TtlCache($this->cacheLifetime);
        }

        return new NullCache();
    }
}
