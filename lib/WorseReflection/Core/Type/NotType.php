<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

class NotType extends Type
{
    public Type $type;

    public function __construct(Type $type)
    {
        $this->type = $type;
    }

    public function __toString(): string
    {
        return sprintf('not<%s>', $this->type);
    }

    public function toPhpString(): string
    {
        return '';
    }

    public function accepts(Type $type): Trinary
    {
        return Trinary::maybe();
    }
}
