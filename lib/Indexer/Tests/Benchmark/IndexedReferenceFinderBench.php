<?php

namespace Phpactor\Indexer\Tests\Benchmark;

use Phpactor\Indexer\Adapter\ReferenceFinder\IndexedReferenceFinder;
use Phpactor\Indexer\IndexAgentBuilder;
use Phpactor\TestUtils\Workspace;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Tests\Unit\ByteOffsetTest;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\ReflectorBuilder;

class IndexedReferenceFinderBench
{
    private IndexedReferenceFinder $finder;

    private TextDocument $document;


    protected function workspace(): Workspace
    {
        return Workspace::create(__DIR__ . '/../Workspace');
    }

    public function __construct()
    {
        $this->workspace()->reset();
        $agent = IndexAgentBuilder::create(
            $this->workspace()->path('.index'),
            __DIR__ . '/fixture',
        )->buildAgent();
        $agent->indexer()->getJob()->run();

        $this->document = TextDocumentBuilder::fromUri(__DIR__ . '/fixture/SyliusSpec.php')->build();
        $reflector = ReflectorBuilder::create()->addSource($this->document->__toString())->build();
        $this->finder = new IndexedReferenceFinder($agent->query(), $reflector);
    }

    public function benchBareFileSearch(): void
    {
        foreach ($this->finder->findReferences($this->document, ByteOffset::fromInt(6009)) as $reference) {
        }
    }
}
