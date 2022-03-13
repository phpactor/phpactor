<?php

namespace Phpactor\ClassMover\Domain\Name;

use Phpactor\ClassMover\Domain\Reference\ImportedNameReference;
use RuntimeException;

class NameImportTable
{
    private $namespace;

    private $importedNameRefs = [];

    private function __construct(Namespace_ $namespace, array $importedNamespaceNames)
    {
        $this->namespace = $namespace;
        foreach ($importedNamespaceNames as $importedNamespaceName) {
            $this->addImportedName($importedNamespaceName);
        }
    }

    public static function fromImportedNameRefs(Namespace_ $namespace, array $importedNameRefs): NameImportTable
    {
        return new self($namespace, $importedNameRefs);
    }

    public function isNameImported(QualifiedName $name)
    {
        foreach ($this->importedNameRefs as $importedNameRef) {
            if ($importedNameRef->importedName()->qualifies($name)) {
                return true;
            }
        }

        return false;
    }

    public function getImportedNameRefFor(QualifiedName $name): ?ImportedNameReference
    {
        foreach ($this->importedNameRefs as $importedNameRef) {
            if ($importedNameRef->importedName()->qualifies($name)) {
                return $importedNameRef;
            }
        }

        throw new RuntimeException(sprintf(
            'Could not find name in import table "%s"',
            (string)$name
        ));
    }

    public function resolveClassName(QualifiedName $name)
    {
        foreach ($this->importedNameRefs as $importedNameRef) {
            if ($importedNameRef->importedName()->qualifies($name)) {
                return $importedNameRef->importedName()->qualify($name);
            }
        }

        if (0 === strpos($name->__toString(), '\\')) {
            return FullyQualifiedName::fromString($name->__toString());
        }

        return $this->namespace->qualify($name);
    }

    public function namespace(): Namespace_
    {
        return $this->namespace;
    }

    public function isAliased(QualifiedName $name)
    {
        foreach ($this->importedNameRefs as $importedNameRef) {
            if ($importedNameRef->importedName()->qualifies($name)) {
                return $importedNameRef->importedName()->isAlias();
            }
        }

        return false;
    }

    private function addImportedName(ImportedNameReference $importedNameRef): void
    {
        $this->importedNameRefs[] = $importedNameRef;
    }
}
