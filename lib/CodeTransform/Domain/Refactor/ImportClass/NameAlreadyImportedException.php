<?php

namespace Phpactor\CodeTransform\Domain\Refactor\ImportClass;

class NameAlreadyImportedException extends NameAlreadyUsedException
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $existingName;

    /**
     * @var string
     */
    private $existingFQN;

    public function __construct(NameImport $nameImport, string $existingName, string $existingFQN)
    {
        parent::__construct(sprintf(
            '%s "%s" is already imported',
            ucfirst($nameImport->type()),
            $nameImport->name()->head()
        ));

        $this->name = $nameImport->name()->head()->__toString();
        $this->existingName = $existingName;
        $this->existingFQN = $existingFQN;
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
