<?php

namespace Phpactor\ClassMover\Domain\Reference;

use Phpactor\ClassMover\Domain\Name\ImportedName;

final class ImportedNameReference
{
    private bool $exists;

    private function __construct(
        private ?Position $position = null,
        private ?ImportedName $importedName = null
    ) {
    }

    public function __toString(): string
    {
        return (string) $this->importedName;
    }

    public static function none(): self
    {
        $new = new self();
        $new->exists = false;

        return $new;
    }

    public static function fromImportedNameAndPosition(ImportedName $importedName, Position $position): ImportedNameReference
    {
        return new self($position, $importedName);
    }

    public function exists(): bool
    {
        return $this->exists;
    }

    public function position(): ?Position
    {
        return $this->position;
    }

    public function importedName(): ?ImportedName
    {
        return $this->importedName;
    }
}
