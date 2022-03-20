<?php

namespace Phpactor\WorseReflection\Tests\Benchmarks;

use Phpactor\TestUtils\Workspace;
use Phpactor\WorseReflection\Bridge\Composer\ComposerSourceLocator;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StubSourceLocator;
use Phpactor\WorseReflection\ReflectorBuilder;

/**
 * @BeforeMethods({"setUp"})
 */
abstract class BaseBenchCase
{
    private Reflector $reflector;

    public function setUp(): void
    {
        $composerLocator = new ComposerSourceLocator(include(__DIR__ . '/../../vendor/autoload.php'));

        $workspace = new Workspace(__DIR__ . '/../Workspace');
        $workspace->reset();
        $stubLocator = new StubSourceLocator(
            ReflectorBuilder::create()->build(),
            __DIR__ . '/../../vendor/jetbrains/phpstorm-stubs',
            $workspace->path('/')
        );

        $this->reflector = ReflectorBuilder::create()
            ->addLocator($composerLocator)
            ->addLocator($stubLocator)
            ->enableCache()
            ->cacheLifetime(5)
            ->enableContextualSourceLocation()
            ->build();
    }

    public function getReflector(): Reflector
    {
        return $this->reflector;
    }
}
