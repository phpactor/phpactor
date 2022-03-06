<?php

namespace Phpactor\ClassMover\Domain\Reference;

use Phpactor\ClassMover\Domain\Name\ImportedName;

final class ImportedNameReference
{
    /**
     * @var Position|null
     */
    private $position;

    /**
     * @var ImportedName|null
     */
    private $importedName;

    /**
     * @var bool
     */
    private $exists;

    private function __construct(Position $position = null, ImportedName $importedName = null)
    {
        $this->position = $position;
        $this->importedName = $importedName;
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
