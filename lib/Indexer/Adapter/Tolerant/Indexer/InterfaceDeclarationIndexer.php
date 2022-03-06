<?php

namespace Phpactor\Indexer\Adapter\Tolerant\Indexer;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\Indexer\Model\Index;
use Phpactor\TextDocument\TextDocument;

class InterfaceDeclarationIndexer extends AbstractClassLikeIndexer
{
    public function canIndex(Node $node): bool
    {
        return $node instanceof InterfaceDeclaration;
    }

    public function index(Index $index, TextDocument $document, Node $node): void
    {
        assert($node instanceof InterfaceDeclaration);
        $record = $this->getClassLikeRecord('interface', $node, $index, $document);

        // remove any references to this interface and other classes before
        // updating with the current data
        $this->removeImplementations($index, $record);
        $record->clearImplemented();

        $this->indexImplementedInterfaces($index, $record, $node);

        $index->write($record);
    }

    private function indexImplementedInterfaces(Index $index, ClassRecord $classRecord, InterfaceDeclaration $node): void
    {
        if (null === $interfaceClause = $node->interfaceBaseClause) {
            return;
        }

        if (null == $interfaceList = $interfaceClause->interfaceNameList) {
            return;
        }

        $this->indexInterfaceList($interfaceList, $classRecord, $index);
    }
}
