<?php

namespace Phpactor\Indexer\Adapter\Tolerant\Indexer;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\EnumDeclaration;
use Microsoft\PhpParser\Node\TraitUseClause;
use Phpactor\Indexer\Adapter\Tolerant\TolerantIndexer;
use Phpactor\Indexer\Model\Index;
use Phpactor\Indexer\Model\Name\FullyQualifiedName;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\WorseReflection\Bridge\TolerantParser\Patch\TolerantQualifiedNameResolver;
use Phpactor\TextDocument\TextDocument;

class TraitUseClauseIndexer implements TolerantIndexer
{
    public function canIndex(Node $node): bool
    {
        return $node instanceof TraitUseClause;
    }

    public function index(Index $index, TextDocument $document, Node $node): void
    {
        assert($node instanceof TraitUseClause);

        if (null === $node->traitNameList) {
            return;
        }

        foreach ($node->traitNameList->children as $qualifiedName) {
            if (!$qualifiedName instanceof QualifiedName) {
                continue;
            }

            /** @var ClassDeclaration|EnumDeclaration|null $parentDeclaration */
            $parentDeclaration = $node->getFirstAncestor(ClassDeclaration::class, EnumDeclaration::class);

            if ($parentDeclaration === null) {
                continue;
            }

            $traitRecord = $index->get(ClassRecord::fromName(
                // This call is a hack from WorseReflection (!) because of a bug in
                // the tolerant PHP parser which does not provide the resolved
                // use namespace.
                TolerantQualifiedNameResolver::getResolvedName($qualifiedName)
            ));

            assert($traitRecord instanceof ClassRecord);
            $traitRecord->addImplementation(FullyQualifiedName::fromString($parentDeclaration->getNamespacedName()->__toString()));
            $index->write($traitRecord);
        }
    }

    public function beforeParse(Index $index, TextDocument $document): void
    {
    }
}
