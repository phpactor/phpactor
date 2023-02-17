<?php

namespace Phpactor\Indexer\Adapter\Tolerant\Indexer;

use Microsoft\PhpParser\MissingToken;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\EnumDeclaration;
use Phpactor\Indexer\Model\Exception\CannotIndexNode;
use Phpactor\Indexer\Model\Record\ClassRecord;
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
        if ($node->name instanceof MissingToken) {
            throw new CannotIndexNode(sprintf(
                'Class name is missing (maybe a reserved word) in: %s',
                $document->uri()->path()
            ));
        }
        $record = $this->getClassLikeRecord(ClassRecord::TYPE_ENUM, $node, $index, $document);

        $this->removeImplementations($index, $record);
        $record->clearImplemented();

        $index->write($record);
    }
}
