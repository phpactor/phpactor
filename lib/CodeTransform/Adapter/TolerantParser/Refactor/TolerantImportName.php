<?php

namespace Phpactor\CodeTransform\Adapter\TolerantParser\Refactor;

use Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider;
use Microsoft\PhpParser\ClassLike;
use Microsoft\PhpParser\NamespacedNameInterface;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\ResolvedName;
use Phpactor\CodeTransform\Domain\Refactor\ImportClass\NameImport;
use Phpactor\CodeTransform\Domain\Refactor\ImportName;
use Phpactor\WorseReflection\Core\AstProvider;
use Phpactor\CodeTransform\Domain\SourceCode;
use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\CodeTransform\Domain\Refactor\ImportClass\NameAlreadyImportedException;
use Phpactor\CodeTransform\Domain\ClassName;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Microsoft\PhpParser\Node;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeTransform\Domain\Refactor\ImportClass\AliasAlreadyUsedException;
use Phpactor\CodeTransform\Domain\Refactor\ImportClass\ClassIsCurrentClassException;
use Phpactor\CodeTransform\Domain\Refactor\ImportClass\NameAlreadyInNamespaceException;
use Phpactor\Name\FullyQualifiedName;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;

class TolerantImportName implements ImportName
{
    public function __construct(
        private Updater $updater,
        private AstProvider $parser = new TolerantAstProvider(),
        private bool $importGlobals = false,
    ) {
    }

    public function importName(SourceCode $source, ByteOffset $offset, NameImport $nameImport): TextEdits
    {
        if ($this->isGlobalFunction($nameImport)) {
            return TextEdits::none();
        }

        $sourceNode = $this->parser->get($source);
        $node = $this->getLastNodeAtPosition($sourceNode, $offset);

        $this->assertNotAlreadyImported($node, $nameImport);

        $edits = $this->addImport($source, $nameImport);

        if ($nameImport->alias() !== null) {
            $edits = $this->updateReferences($node, $nameImport, $edits);
        }

        return $edits;
    }

    public function importNameOnly(SourceCode $source, ByteOffset $offset, NameImport $nameImport): TextEdits
    {
        if ($this->isGlobalFunction($nameImport)) {
            return TextEdits::none();
        }

        $sourceNode = $this->parser->get($source);
        $node = $this->getLastNodeAtPosition($sourceNode, $offset);

        $this->assertNotAlreadyImported($node, $nameImport);

        return $this->addImport($source, $nameImport);
    }

    private function assertNotAlreadyImported(Node $node, NameImport $nameImport): void
    {
        $currentClass = $this->currentClass($node);
        $imports = $node->getImportTablesForCurrentScope()[$this->resolveImportTableOffset($nameImport)];

        [ $existingName, $existingImport ] = $this->findExistingImport($nameImport, $imports);
        if (null === $nameImport->alias() && $existingImport !== null) {
            throw new NameAlreadyImportedException(
                $nameImport,
                $existingName,
                $existingImport->getFullyQualifiedNameText()
            );
        }

        if (null === $nameImport->alias() && $currentClass && $currentClass->short() === $nameImport->name()->head()->__toString()) {
            throw new NameAlreadyImportedException($nameImport, $currentClass->short(), $currentClass->__toString());
        }

        if ($nameImport->alias() && isset($imports[$nameImport->alias()])) {
            throw new AliasAlreadyUsedException($nameImport);
        }

        if ($nameImport->isClass() && $this->currentClassIsSameAsImportClass($node, $nameImport->name())) {
            throw new ClassIsCurrentClassException($nameImport);
        }

        if ($this->importClassInSameNamespace($node, $nameImport->name())) {
            throw new NameAlreadyInNamespaceException($nameImport);
        }
    }

    /**
     * @param array<ResolvedName> $imports
     */
    private function findExistingImport(NameImport $nameImport, array $imports): ?array
    {
        $nameImportParts = $nameImport->name()->toArray();

        foreach ($imports as $name => $import) {
            if ($import->getNameParts() === $nameImportParts) {
                // fqn already used in imports
                return [$name, $import];
            }
        }

        $shortName = $nameImport->name()->head()->__toString();
        if (array_key_exists($shortName, $imports)) {
            // short name already used in imports
            return [$shortName, $imports[$shortName]];
        }

        return null;
    }

    private function currentClassIsSameAsImportClass(Node $node, FullyQualifiedName $className): bool
    {
        if (!$node instanceof ClassLike || !$node instanceof NamespacedNameInterface) {
            return false;
        }

        if ((string) $node->getNamespacedName() === (string) $className) {
            return true;
        }

        return false;
    }

    private function addImport(SourceCode $source, NameImport $nameImport): TextEdits
    {
        $builder = SourceCodeBuilder::create();

        $this->addUse($builder, $nameImport);
        $prototype = $builder->build();

        return $this->updater->textEditsFor($prototype, Code::fromString((string) $source));
    }

    private function importClassInSameNamespace(Node $node, FullyQualifiedName $className): bool
    {
        $namespace = '';
        if ($definition = $node->getNamespaceDefinition()) {
            $namespace = (string) $definition->getFirstChildNode(QualifiedName::class);
        }

        if ($className->count() > 1 && $className->tail()->__toString() == $namespace) {
            return true;
        }

        return false;
    }

    private function updateReferences(Node $node, NameImport $nameImport, TextEdits $edits): TextEdits
    {
        $alias = $nameImport->alias();

        if (is_null($alias)) {
            return $edits;
        }

        return $edits->add(TextEdit::create(
            $node->getStartPosition(),
            $node->getEndPosition() - $node->getStartPosition(),
            $alias
        ));
    }

    private function currentClass(Node $node): ?ClassName
    {
        $classDeclaration = $node->getFirstAncestor(ClassLike::class);

        if (!$classDeclaration instanceof NamespacedNameInterface) {
            return null;
        }


        $name = (string)$classDeclaration->getNamespacedName();

        if (!$name) {
            return null;
        }

        return ClassName::fromString($name);
    }

    private function resolveImportTableOffset(NameImport $nameImport): int
    {
        return $nameImport->isFunction() ? 1 : 0;
    }

    private function addUse(SourceCodeBuilder $builder, NameImport $nameImport): void
    {
        if ($nameImport->isFunction()) {
            $builder->useFunction($nameImport->name()->__toString(), $nameImport->alias());
            return;
        }

        $builder->use($nameImport->name()->__toString(), $nameImport->alias());
    }

    private function getLastNodeAtPosition(SourceFileNode $sourceNode, ByteOffset $offset): Node
    {
        $node = $sourceNode->getDescendantNodeAtPosition($offset->toInt());

        /*
         * In case the cursor is not on a recognized node we need to find the
         * first available node after the cusror in order to make sure the
         * import table will be loaded.
         */
        if ($node instanceof SourceFileNode) {
            /** @var Node $childNode */
            foreach ($node->getChildNodes() as $childNode) {
                if ($childNode->getStartPosition() > $offset->toInt()) {
                    break;
                }

                $node = $childNode;
            }
        }

        return $node;
    }

    private function isGlobalFunction(NameImport $nameImport): bool
    {
        return $this->importGlobals === false && $nameImport->isFunction() && $nameImport->name()->count() === 1;
    }
}
