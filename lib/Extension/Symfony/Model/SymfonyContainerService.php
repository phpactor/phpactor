<?php

namespace Phpactor\Extension\Symfony\Model;

use Phpactor\WorseReflection\Core\Type;

class SymfonyContainerService
{
    public string $id;

    public Type $type;

    public function __construct(string $id, Type $type)
    {
        $this->id = $id;
        $this->type = $type;
    }
}
