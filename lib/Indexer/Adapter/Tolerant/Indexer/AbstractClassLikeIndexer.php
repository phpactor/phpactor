<?php

namespace Phpactor\Indexer\Adapter\Tolerant\Indexer;

use Microsoft\PhpParser\NamespacedNameInterface;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\DelimitedList\QualifiedNameList;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\EnumDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Phpactor\Indexer\Adapter\Tolerant\TolerantIndexer;
use Phpactor\Indexer\Model\Exception\CannotIndexNode;
use Phpactor\Indexer\Model\Name\FullyQualifiedName;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\Indexer\Model\Index;
use Phpactor\TextDocument\TextDocument;

abstract class AbstractClassLikeIndexer implements TolerantIndexer
{
    public function beforeParse(Index $index, TextDocument $document): void
    {
    }

    protected function removeImplementations(Index $index, ClassRecord $record): void
    {
        foreach ($record->implements() as $implementedClass) {
            $implementedRecord = $index->get(ClassRecord::fromName($implementedClass));

            if (false === $implementedRecord->removeImplementation($record->fqn())) {
                continue;
            }

            $index->write($implementedRecord);
        }
    }

    protected function indexInterfaceList(QualifiedNameList $interfaceList, ClassRecord $record, Index $index): void
    {
        foreach ($interfaceList->children as $interfaceName) {
            if (!$interfaceName instanceof QualifiedName) {
                continue;
            }

            $interfaceName = (string) $interfaceName->getResolvedName();
            $interfaceRecord = $index->get(ClassRecord::fromName($interfaceName));
            $record->addImplements(
                FullyQualifiedName::fromString($interfaceName)
            );

            assert($interfaceRecord instanceof ClassRecord);
            $interfaceRecord->addImplementation($record->fqn());

            $index->write($interfaceRecord);
        }
    }

    /**
     * @param ClassRecord::TYPE_* $type
     */
    protected function getClassLikeRecord(string $type, Node $node, Index $index, TextDocument $document): ClassRecord
    {
        assert($node instanceof NamespacedNameInterface);
        $name = $node->getNamespacedName()->getFullyQualifiedNameText();

        if (empty($name)) {
            throw new CannotIndexNode(sprintf(
                'Name is empty for file "%s"',
                $document->uri()->__toString()
            ));
        }

        $record = $index->get(ClassRecord::fromName($name));
        assert($record instanceof ClassRecord);
        /** @var ClassDeclaration|InterfaceDeclaration|EnumDeclaration|TraitDeclaration $node */
        $record->setStart(ByteOffset::fromInt($node->name->getStartPosition()));
        $record->setEnd(ByteOffset::fromInt($node->name->getEndPosition()));
        $record->setFilePath($document->uri()->__toString());
        $record->setType($type);

        return $record;
    }
}
