<?php

namespace Phpactor\WorseReferenceFinder\Tests;

use PHPUnit\Framework\TestCase;
use Phpactor\TestUtils\Workspace;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StubSourceLocator;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\ReflectorBuilder;

abstract class IntegrationTestCase extends TestCase
{
    protected Workspace $workspace;

    public function setUp(): void
    {
        $this->workspace = Workspace::create(__DIR__ . '/Workspace');
        $this->workspace->reset();
    }

    protected function reflector(): Reflector
    {
        return ReflectorBuilder::create()
            ->enableContextualSourceLocation()
            ->addLocator(new StubSourceLocator(
                ReflectorBuilder::create()->build(),
                $this->workspace->path(''),
                $this->workspace->path('cache')
            ))
            ->build();
    }
}
