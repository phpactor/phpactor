<?php

namespace Phpactor\WorseReflection\Core\DocBlock;

use Phpactor\WorseReflection\Core\Type;

class DocBlockParam
{
    public function __construct(
        private string $name,
        private Type $type
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): Type
    {
        return $this->type;
    }
}
