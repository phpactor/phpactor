<?php

namespace Phpactor\CodeTransform\Domain\Refactor\ImportClass;

class AliasAlreadyUsedException extends NameAlreadyUsedException
{
    private readonly string $name;

    public function __construct(NameImport $nameImport)
    {
        parent::__construct(sprintf(
            '%s alias "%s" is already used',
            ucfirst($nameImport->type()),
            $nameImport->alias()
        ));

        $this->name = $nameImport->name()->head()->__toString();
    }

    public function name(): string
    {
        return $this->name;
    }
}
