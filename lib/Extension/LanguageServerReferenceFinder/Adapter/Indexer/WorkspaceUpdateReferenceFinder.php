<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Adapter\Indexer;

use Generator;
use Phpactor\Indexer\Model\Indexer;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\ReferenceFinder\ReferenceFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Exception\TextDocumentNotFound;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentBuilder;

class WorkspaceUpdateReferenceFinder implements ReferenceFinder
{
    private Workspace $workspace;

    private Indexer $indexer;

    private ReferenceFinder $innerReferenceFinder;

    public function __construct(Workspace $workspace, Indexer $indexer, ReferenceFinder $innerReferenceFinder)
    {
        $this->workspace = $workspace;
        $this->indexer = $indexer;
        $this->innerReferenceFinder = $innerReferenceFinder;
    }

    
    public function findReferences(TextDocument $document, ByteOffset $byteOffset): Generator
    {
        $this->indexWorkspace();

        yield from $this->innerReferenceFinder->findReferences($document, $byteOffset);
    }

    private function indexWorkspace(): void
    {
        // ensure that the index is current with the workspace
        foreach ($this->workspace as $document) {
            assert($document instanceof TextDocumentItem);
            try {
                $this->indexer->indexDirty(
                    TextDocumentBuilder::fromUri($document->uri)->text($document->text)->build()
                );
            } catch (TextDocumentNotFound $e) {
            }
        }
    }
}
