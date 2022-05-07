<?php

namespace Phpactor\Indexer\Adapter\Tolerant\Indexer;

use Microsoft\PhpParser\Node;
use Phpactor\Indexer\Model\Name\FullyQualifiedName;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Microsoft\PhpParser\Node\Statement\EnumDeclaration;
use Phpactor\TextDocument\TextDocument;
use Phpactor\Indexer\Model\Index;

class EnumDeclarationIndexer extends AbstractClassLikeIndexer
{
    public function canIndex(Node $node): bool
    {
        return $node instanceof EnumDeclaration;
    }

    public function index(Index $index, TextDocument $document, Node $node): void
    {
        assert($node instanceof EnumDeclaration);
        $record = $this->getClassLikeRecord('class', $node, $index, $document);

        $this->removeImplementations($index, $record);
        $record->clearImplemented();

        $index->write($record);
    }
}
