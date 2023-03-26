<?php

namespace Phpactor\WorseReflection\Tests\Integration;

use Phpactor\TestUtils\Workspace;
use Phpactor\WorseReflection\Bridge\Phpactor\MemberProvider\DocblockMemberProvider;
use Phpactor\WorseReflection\Core\Inference\Walker\TestAssertWalker;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StubSourceLocator;
use Phpactor\WorseReflection\Reflector;
use PHPUnit\Framework\TestCase;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Node\SourceFileNode;
use Phpactor\WorseReflection\Bridge\PsrLog\ArrayLogger;
use Phpactor\WorseReflection\ReflectorBuilder;

class IntegrationTestCase extends TestCase
{
    private ArrayLogger $logger;

    public function setUp(): void
    {
        $this->logger = new ArrayLogger();
    }

    public function createBuilder(TextDocument|string $source): ReflectorBuilder
    {
        return ReflectorBuilder::create()
            ->addSource($source)
            ->addMemberProvider(new DocblockMemberProvider())
            ->addFrameWalker(new TestAssertWalker($this))
            ->withLogger($this->logger());
    }

    public function createReflector(string $source): Reflector
    {
        return $this->createBuilder($source)->build();
    }

    public function createWorkspaceReflector(string $source): Reflector
    {
        return ReflectorBuilder::create()
            ->addLocator(new StubSourceLocator(
                ReflectorBuilder::create()->build(),
                $this->workspace()->path('/'),
                $this->workspace()->path('/')
            ))
            ->addMemberProvider(new DocblockMemberProvider())
            ->withLogger($this->logger())->build();
    }

    protected function logger(): ArrayLogger
    {
        return $this->logger;
    }

    protected function workspace(): Workspace
    {
        return new Workspace(__DIR__ . '/../Workspace');
    }

    protected function parseSource(string $source, string $uri = null): SourceFileNode
    {
        $parser = new Parser();

        return $parser->parseSourceFile($source, $uri);
    }
}
