<?php

namespace Phpactor\WorseReflection\Core\DocBlock;

use Phpactor\WorseReflection\Core\Type;

final class DocBlockTypeAlias
{
    public function __construct(
        private readonly string $alias,
        private readonly Type $type
    ) {
    }

    public function alias(): string
    {
        return $this->alias;
    }

    public function type(): Type
    {
        return $this->type;
    }
}
