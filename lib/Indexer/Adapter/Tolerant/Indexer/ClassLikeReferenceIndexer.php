<?php

namespace Phpactor\Indexer\Adapter\Tolerant\Indexer;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\TraitUseClause;
use Phpactor\Indexer\Model\Index;
use Phpactor\Indexer\Model\RecordReference;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\Indexer\Model\Record\FileRecord;
use Phpactor\WorseReflection\Bridge\TolerantParser\Patch\TolerantQualifiedNameResolver;
use Phpactor\TextDocument\TextDocument;

class ClassLikeReferenceIndexer extends AbstractClassLikeIndexer
{
    private const NOT_CLASS_NAMES = [
        'static',
        'parent',
        'self'
    ];

    public function canIndex(Node $node): bool
    {
        // its hard to tell what is a class and what is not but all function
        // calls have parent that is instanceof CallExpression
        return $node instanceof QualifiedName && !$node->parent instanceof CallExpression;
    }

    public function beforeParse(Index $index, TextDocument $document): void
    {
        $fileRecord = $index->get(FileRecord::fromPath($document->uri()->path()));
        assert($fileRecord instanceof FileRecord);

        foreach ($fileRecord->references() as $outgoingReference) {
            if ($outgoingReference->type() !== ClassRecord::RECORD_TYPE) {
                continue;
            }

            $record = $index->get(ClassRecord::fromName($outgoingReference->identifier()));
            assert($record instanceof ClassRecord);
            $record->removeReference($fileRecord->identifier());
            $index->write($record);
            $fileRecord->removeReferencesToRecordType($outgoingReference->type());
            $index->write($fileRecord);
        }
    }

    public function index(Index $index, TextDocument $document, Node $node): void
    {
        assert($node instanceof QualifiedName);

        $name =
            $node->parent?->parent instanceof TraitUseClause ?
                TolerantQualifiedNameResolver::getResolvedName($node) :
                $node->getResolvedName();

        if (empty($name)) {
            return;
        }

        if (in_array((string)$name, self::NOT_CLASS_NAMES)) {
            return;
        }

        $targetRecord = $index->get(ClassRecord::fromName($name));
        assert($targetRecord instanceof ClassRecord);
        $targetRecord->addReference($document->uri()->path());

        $index->write($targetRecord);

        $fileRecord = $index->get(FileRecord::fromPath($document->uri()->path()));
        assert($fileRecord instanceof FileRecord);
        $reference = new RecordReference(
            ClassRecord::RECORD_TYPE,
            $targetRecord->identifier(),
            $node->getStartPosition(),
            end: $node->getEndPosition()
        );

        if ($node->parent instanceof ObjectCreationExpression) {
            $reference->addFlag(RecordReference::FLAG_NEW_OBJECT);
        }

        $fileRecord->addReference($reference);
        $index->write($fileRecord);
    }
}
