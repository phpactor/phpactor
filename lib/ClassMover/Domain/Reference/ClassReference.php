<?php

namespace Phpactor\ClassMover\Domain\Reference;

use Phpactor\ClassMover\Domain\Name\QualifiedName;
use Phpactor\ClassMover\Domain\Name\FullyQualifiedName;

final class ClassReference
{
    private Position $position;

    private FullyQualifiedName $fullName;

    private QualifiedName $name;

    private bool $isClassDeclaration;

    private ImportedNameReference $importedNameRef;

    private bool $hasAlias = false;

    private bool $isImport = false;

    public function __toString(): string
    {
        return (string) $this->fullName;
    }

    public static function fromNameAndPosition(
        QualifiedName $referencedName,
        FullyQualifiedName $fullName,
        Position $position,
        ImportedNameReference $importedNameRef,
        bool $isClassDeclaration = false,
        bool $hasAlias = false,
        bool $isImport = false
    ): self {
        $new = new self();
        $new->position = $position;
        $new->name = $referencedName;
        $new->fullName = $fullName;
        $new->importedNameRef = $importedNameRef;
        $new->isClassDeclaration = $isClassDeclaration;
        $new->hasAlias = $hasAlias;
        $new->isImport = $isImport;

        return $new;
    }

    public function position(): Position
    {
        return $this->position;
    }

    public function name(): QualifiedName
    {
        return $this->name;
    }

    public function fullName(): FullyQualifiedName
    {
        return $this->fullName;
    }

    public function importedNameRef(): ImportedNameReference
    {
        return $this->importedNameRef;
    }

    public function isClassDeclaration(): bool
    {
        return $this->isClassDeclaration;
    }

    public function hasAlias(): bool
    {
        return $this->hasAlias;
    }

    public function isImport(): bool
    {
        return $this->isImport;
    }
}
