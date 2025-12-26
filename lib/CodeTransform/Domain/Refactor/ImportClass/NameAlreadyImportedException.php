<?php

namespace Phpactor\CodeTransform\Domain\Refactor\ImportClass;

class NameAlreadyImportedException extends NameAlreadyUsedException
{
    private readonly string $name;

    public function __construct(
        NameImport $nameImport,
        private readonly string $existingName,
        private readonly string $existingFQN
    ) {
        parent::__construct(sprintf(
            '%s "%s" is already imported',
            ucfirst($nameImport->type()),
            $nameImport->name()->head()
        ));

        $this->name = $nameImport->name()->head()->__toString();
    }

    public function name(): string
    {
        return $this->name;
    }

    public function existingName(): string
    {
        return $this->existingName;
    }

    public function existingFQN(): string
    {
        return $this->existingFQN;
    }
}
