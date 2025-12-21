<?php

namespace Phpactor\ClassMover\Domain\Name;

use Phpactor\ClassMover\Domain\Reference\ImportedNameReference;
use RuntimeException;

class NameImportTable
{
    /** @var ImportedNameReference[] */
    private array $importedNameRefs = [];

    /** @param ImportedNameReference[] $importedNamespaceNames */
    private function __construct(
        private Namespace_ $namespace,
        array $importedNamespaceNames
    ) {
        foreach ($importedNamespaceNames as $importedNamespaceName) {
            $this->addImportedName($importedNamespaceName);
        }
    }

    /** @param ImportedNameReference[] $importedNameRefs */
    public static function fromImportedNameRefs(Namespace_ $namespace, array $importedNameRefs): NameImportTable
    {
        return new self($namespace, $importedNameRefs);
    }

    public function isNameImported(QualifiedName $name): bool
    {
        foreach ($this->importedNameRefs as $importedNameRef) {
            if ($importedNameRef->importedName()?->qualifies($name)) {
                return true;
            }
        }

        return false;
    }

    public function getImportedNameRefFor(QualifiedName $name): ?ImportedNameReference
    {
        foreach ($this->importedNameRefs as $importedNameRef) {
            if ($importedNameRef->importedName()?->qualifies($name)) {
                return $importedNameRef;
            }
        }

        throw new RuntimeException(sprintf(
            'Could not find name in import table "%s"',
            (string)$name
        ));
    }

    public function resolveClassName(QualifiedName $name): FullyQualifiedName
    {
        foreach ($this->importedNameRefs as $importedNameRef) {
            if ($importedNameRef->importedName()?->qualifies($name)) {
                return $importedNameRef->importedName()->qualify($name);
            }
        }

        if (str_starts_with($name->__toString(), '\\')) {
            return FullyQualifiedName::fromString($name->__toString());
        }

        return $this->namespace->qualify($name);
    }

    public function namespace(): Namespace_
    {
        return $this->namespace;
    }

    public function isAliased(QualifiedName $name): bool
    {
        foreach ($this->importedNameRefs as $importedNameRef) {
            $importedName = $importedNameRef->importedName();
            if ($importedName === null) {
                continue;
            }

            if ($importedName->qualifies($name)) {
                return $importedName->isAlias();
            }
        }

        return false;
    }

    private function addImportedName(ImportedNameReference $importedNameRef): void
    {
        $this->importedNameRefs[] = $importedNameRef;
    }
}
