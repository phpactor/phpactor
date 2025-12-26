<?php

namespace Phpactor\WorseReflection\Core\DocBlock;

use Phpactor\WorseReflection\Core\Type;

class DocBlockVar
{
    public function __construct(
        private readonly string $name,
        private readonly Type $type
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
