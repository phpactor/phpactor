<?php

namespace Phpactor\ClassMover\Adapter\TolerantParser;

use Microsoft\PhpParser\MissingToken;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\NamespaceAliasingClause;
use Microsoft\PhpParser\Node\NamespaceUseClause;
use Microsoft\PhpParser\Node\QualifiedName as ParserQualifiedName;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node\Statement\NamespaceDefinition;
use Microsoft\PhpParser\Node\Statement\NamespaceUseDeclaration;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Microsoft\PhpParser\Parser;
use Phpactor\ClassMover\Domain\Reference\ClassReference;
use Phpactor\ClassMover\Domain\Name\FullyQualifiedName;
use Phpactor\ClassMover\Domain\Name\ImportedName;
use Phpactor\ClassMover\Domain\Reference\ImportedNameReference;
use Phpactor\ClassMover\Domain\Reference\NamespaceReference;
use Phpactor\ClassMover\Domain\Reference\NamespacedClassReferences;
use Phpactor\ClassMover\Domain\Reference\Position;
use Phpactor\ClassMover\Domain\Name\QualifiedName;
use Phpactor\ClassMover\Domain\ClassFinder;
use Phpactor\ClassMover\Domain\Name\NameImportTable;
use Phpactor\ClassMover\Domain\Name\Namespace_;
use Phpactor\TextDocument\TextDocument;

class TolerantClassFinder implements ClassFinder
{
    private Parser $parser;

    public function __construct(Parser $parser = null)
    {
        $this->parser = $parser ?: new Parser();
    }

    public function findIn(TextDocument $source): NamespacedClassReferences
    {
        $ast = $this->parser->parseSourceFile($source->__toString());

        $namespaceRef = $this->getNamespaceRef($ast);
        $sourceEnvironment = $this->getClassEnvironment($namespaceRef->namespace(), $ast);

        $classRefs = $this->resolveClassNames($source, $sourceEnvironment, $ast);

        return NamespacedClassReferences::fromNamespaceAndClassRefs($namespaceRef, $classRefs);
    }

    /** @return array<ClassReference> */
    private function resolveClassNames(TextDocument $source, NameImportTable $env, SourceFileNode $ast): array
    {
        $classRefs = [];
        $nodes = $ast->getDescendantNodes();

        foreach ($nodes as $node) {
            if (
                $node instanceof ClassDeclaration ||
                $node instanceof InterfaceDeclaration ||
                $node instanceof TraitDeclaration
            ) {
                $name = (string) $node->name->getText($node->getFileContents());

                if (!$name) {
                    continue;
                }

                $classRefs[] = ClassReference::fromNameAndPosition(
                    QualifiedName::fromString($name),
                    FullyQualifiedName::fromString($node->getNamespacedName()->getFullyQualifiedNameText()),
                    Position::fromStartAndEnd($node->name->start, $node->name->start + $node->name->length - 1),
                    ImportedNameReference::none(),
                    true
                );
                continue;
            }

            // we want QualifiedNames
            if (!$node instanceof ParserQualifiedName) {
                continue;
            }

            // (the) namepspace definition is not interesting
            if ($node->getParent() instanceof NamespaceDefinition) {
                continue;
            }

            if ($node->getParent() instanceof CallExpression) {
                continue;
            }

            $qualifiedName = QualifiedName::fromString($node->getText());

            // we want to replace all fully qualified use statements
            $parentNode = $node->getParent();
            if ($parentNode instanceof NamespaceUseClause) {
                $classRefs[] = ClassReference::fromNameAndPosition(
                    FullyQualifiedName::fromString($node->getText()),
                    FullyQualifiedName::fromString($node->getText()),
                    Position::fromStartAndEnd($node->getStartPosition(), $node->getEndPosition()),
                    ImportedNameReference::none(),
                    false,
                    // @phpstan-ignore-next-line It can be NULL
                    $parentNode->namespaceAliasingClause ? true : false,
                    true,
                );
                continue;
            }

            $resolvedClassName = $env->resolveClassName($qualifiedName);

            // if the name is aliased, then we can safely ignore it
            if ($env->isAliased($qualifiedName)) {
                continue;
            }

            // this is a fully qualified class name
            $importedNameReference = null;
            if ($env->isNameImported($qualifiedName)) {
                $importedNameReference = $env->getImportedNameRefFor($qualifiedName);
            }

            $classRefs[] = ClassReference::fromNameAndPosition(
                $qualifiedName,
                $resolvedClassName,
                Position::fromStartAndEnd($node->getStartPosition(), $node->getEndPosition()),
                $importedNameReference ?? ImportedNameReference::none()
            );
        }

        return $classRefs;
    }

    private function getClassEnvironment(Namespace_ $namespace, SourceFileNode $node): NameImportTable
    {
        $useImportRefs = [];
        foreach ($node->getChildNodes() as $childNode) {
            if (false === $childNode instanceof NamespaceUseDeclaration) {
                continue;
            }

            $this->populateUseImportRefs($childNode, $useImportRefs);
        }

        return NameImportTable::fromImportedNameRefs($namespace, $useImportRefs);
    }

    /**
     * @param array<ImportedNameReference> $useImportRefs
     */
    private function populateUseImportRefs(NamespaceUseDeclaration $useDeclaration, array &$useImportRefs): void
    {
        if (null === $useDeclaration->useClauses) {
            return;
        }

        foreach ($useDeclaration->useClauses->getElements() as $useClause) {
            /** @var NamespaceUseClause $useClause */
            $importedName = ImportedName::fromString((string) $useClause->namespaceName->getText());
            $alias = $importedName;

            /** @var NamespaceAliasingClause|null $aliasClause */
            $aliasClause = $useClause->namespaceAliasingClause;
            if ($useClause->namespaceAliasingClause !== null) {
                $alias = $useClause->namespaceAliasingClause->name->getText($useDeclaration->getFileContents());
                $importedName = $importedName->withAlias((string) $alias);
            }

            $useImportRefs[] = ImportedNameReference::fromImportedNameAndPosition($importedName, Position::fromStartAndEnd(
                $useDeclaration->getStartPosition(),
                $useDeclaration->getEndPosition()
            ));
        }
    }

    private function getNamespaceRef(SourceFileNode $ast): NamespaceReference
    {
        /** @var NamespaceDefinition|null $namespace */
        $namespace = $ast->getFirstDescendantNode(NamespaceDefinition::class);

        if (null === $namespace) {
            return NamespaceReference::forRoot();
        }

        if (null === $namespace->name || $namespace->name instanceof MissingToken) {
            return NamespaceReference::forRoot();
        }

        return NamespaceReference::fromNameAndPosition(
            Namespace_::fromString($namespace->name->getText()),
            Position::fromStartAndEnd(
                $namespace->name->getStartPosition(),
                $namespace->name->getEndPosition()
            )
        );
    }
}
