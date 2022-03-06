<?php

namespace Phpactor\ClassMover\Domain\Reference;

use Phpactor\ClassMover\Domain\Name\QualifiedName;
use Phpactor\ClassMover\Domain\Name\FullyQualifiedName;

final class ClassReference
{
    private $position;
    private $fullName;
    private $name;
    private $isClassDeclaration;
    private $importedNameRef;

    public function __toString()
    {
        return (string) $this->fullName;
    }

    public static function fromNameAndPosition(
        QualifiedName $referencedName,
        FullyQualifiedName $fullName,
        Position $position,
        ImportedNameReference $importedNameRef,
        bool $isClassDeclaration = false
    ) {
        $new = new self();
        $new->position = $position;
        $new->name = $referencedName;
        $new->fullName = $fullName;
        $new->importedNameRef = $importedNameRef;
        $new->isClassDeclaration = $isClassDeclaration;

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
}
