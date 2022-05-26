<?php

namespace Phpactor\WorseReflection\Tests\Benchmarks;

use Phpactor\TestUtils\Workspace;
use Phpactor\WorseReflection\Bridge\Composer\ComposerSourceLocator;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StubSourceLocator;
use Phpactor\WorseReflection\ReflectorBuilder;

/**
 * @BeforeMethods({"setUp"})
 */
abstract class BaseBenchCase
{
    /**
     * @var DiagnosticProvider[]
     */
    protected array $diagnosticProviders = [];

    private Reflector $reflector;

    public function setUp(): void
    {
        $composerLocator = new ComposerSourceLocator(include(__DIR__ . '/../../../../vendor/autoload.php'));

        $workspace = $this->workspace();
        $workspace->reset();
        $stubLocator = new StubSourceLocator(
            ReflectorBuilder::create()->build(),
            __DIR__ . '/../../../../vendor/jetbrains/phpstorm-stubs',
            __DIR__ . '/../Cache',
        );

        $builder = ReflectorBuilder::create();
        foreach ($this->diagnosticProviders as $provider) {
            $builder->addDiagnosticProvider($provider);
        }
        $this->reflector = $builder
            ->addLocator($composerLocator)
            ->addLocator($stubLocator)
            ->enableCache()
            ->cacheLifetime(5)
            ->enableContextualSourceLocation()
            ->build();
    }

    public function loadFixture(string $name): void
    {
        foreach ((array)glob(sprintf('%s/%s/%s/*.php.test', __DIR__, 'fixtures', $name)) as $path) {
            $this->workspace()->put(
                substr(basename((string)$path), 0, -5),
                (string)file_get_contents((string)$path)
            );
        }
    }

    public function getReflector(): Reflector
    {
        return $this->reflector;
    }

    private function workspace(): Workspace
    {
        return new Workspace(__DIR__ . '/../Workspace');
    }
}
