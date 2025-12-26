<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Adapter\Indexer;

use Generator;
use Phpactor\Indexer\Model\Indexer;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\ReferenceFinder\ReferenceFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Exception\TextDocumentNotFound;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentBuilder;

class WorkspaceUpdateReferenceFinder implements ReferenceFinder
{
    /**
     * @var array<string,int>
     */
    private array $documentVersions = [];

    private int $counter = 0;

    public function __construct(
        private readonly Workspace $workspace,
        private readonly Indexer $indexer,
        private readonly ReferenceFinder $innerReferenceFinder
    ) {
    }

    public function findReferences(TextDocument $document, ByteOffset $byteOffset): Generator
    {
        $this->indexWorkspace();

        $generator = $this->innerReferenceFinder->findReferences($document, $byteOffset);
        yield from $generator;
        return $generator->getReturn();
    }

    private function indexWorkspace(): void
    {
        // put an upper limit on the size of the cache
        if ($this->counter++ === 1_000) {
            $this->documentVersions = [];
        }

        // ensure that the index is current with the workspace
        foreach ($this->workspace as $document) {

            // avoid reindexing documents that have not changed
            if (($this->documentVersions[$document->uri] ?? null) === $document->version) {
                continue;
            }
            $this->documentVersions[$document->uri] = $document->version;

            try {
                $this->indexer->indexDirty(
                    TextDocumentBuilder::fromUri($document->uri)->text($document->text)->build()
                );
            } catch (TextDocumentNotFound) {
            }
        }
    }
}
