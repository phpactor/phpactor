<?php

namespace Phpactor\ClassMover\Domain\Reference;

use Phpactor\ClassMover\Domain\Name\ImportedName;

final class ImportedNameReference
{
    private bool $exists;

    private function __construct(private ?Position $position = null, private ?ImportedName $importedName = null)
    {
    }

    public function __toString()
    {
        return (string) $this->importedName;
    }

    public static function none()
    {
        $new = new self();
        $new->exists = false;

        return $new;
    }

    public static function fromImportedNameAndPosition(ImportedName $importedName, Position $position): ImportedNameReference
    {
        return new self($position, $importedName);
    }

    public function exists()
    {
        return $this->exists;
    }

    public function position()
    {
        return $this->position;
    }

    public function importedName()
    {
        return $this->importedName;
    }
}
