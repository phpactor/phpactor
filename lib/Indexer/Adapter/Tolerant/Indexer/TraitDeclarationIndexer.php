<?php

namespace Phpactor\Indexer\Adapter\Tolerant\Indexer;

use Microsoft\PhpParser\MissingToken;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Phpactor\Indexer\Model\Exception\CannotIndexNode;
use Phpactor\Indexer\Model\Index;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\TextDocument\TextDocument;

class TraitDeclarationIndexer extends AbstractClassLikeIndexer
{
    public function canIndex(Node $node): bool
    {
        return $node instanceof TraitDeclaration;
    }

    public function index(Index $index, TextDocument $document, Node $node): void
    {
        assert($node instanceof TraitDeclaration);
        if ($node->name instanceof MissingToken) {
            throw new CannotIndexNode(sprintf(
                'Class name is missing (maybe a reserved word) in: %s',
                $document->uri()?->__toString() ?? '?',
            ));
        }
        $record = $this->getClassLikeRecord(ClassRecord::TYPE_TRAIT, $node, $index, $document);
        $index->write($record);
    }
}
