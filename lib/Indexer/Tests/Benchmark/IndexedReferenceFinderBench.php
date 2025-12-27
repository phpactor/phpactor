<?php

namespace Phpactor\Indexer\Tests\Benchmark;

use Phpactor\Indexer\Adapter\ReferenceFinder\IndexedReferenceFinder;
use Phpactor\Indexer\IndexAgentBuilder;
use Phpactor\TestUtils\Workspace;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\ReflectorBuilder;

class IndexedReferenceFinderBench
{
    private readonly IndexedReferenceFinder $finder;

    private readonly TextDocument $document;

    public function __construct()
    {
        $this->workspace()->reset();
        $this->workspace()->put('SyliusSpec.php', (string)file_get_contents(__DIR__ . '/fixture/SyliusSpec.test'));
        $agent = IndexAgentBuilder::create(
            $this->workspace()->path('.index'),
            $this->workspace()->path(),
        )->buildAgent();
        $agent->indexer()->getJob()->run();

        $this->document = TextDocumentBuilder::fromUri($this->workspace()->path('SyliusSpec.php'))->build();
        $reflector = ReflectorBuilder::create()->addSource($this->document->__toString())->build();
        $this->finder = new IndexedReferenceFinder($agent->query(), $reflector);
    }

    public function benchBareFileSearch(): void
    {
        foreach ($this->finder->findReferences($this->document, ByteOffset::fromInt(6009)) as $reference) {
        }
    }


    protected function workspace(): Workspace
    {
        return Workspace::create(__DIR__ . '/../Workspace');
    }
}
