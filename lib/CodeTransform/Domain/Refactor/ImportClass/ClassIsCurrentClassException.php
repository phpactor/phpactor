<?php

namespace Phpactor\CodeTransform\Domain\Refactor\ImportClass;

use Phpactor\CodeTransform\Domain\Exception\TransformException;

class ClassIsCurrentClassException extends TransformException
{
    private readonly string $name;

    public function __construct(NameImport $nameImport)
    {
        parent::__construct(sprintf(
            '%s "%s" is the current class',
            ucfirst($nameImport->type()),
            $nameImport->name()->head()
        ));

        $this->name = $nameImport->name()->head()->__toString();
    }

    public function name(): string
    {
        return $this->name;
    }
}
