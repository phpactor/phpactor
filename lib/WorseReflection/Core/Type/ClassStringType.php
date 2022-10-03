<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

class ClassStringType extends StringType
{
    private ?ClassName $className;

    public function __construct(?ClassName $className = null)
    {
        $this->className = $className;
    }

    public function __toString(): string
    {
        if ($this->className) {
            return sprintf('class-string<%s>', $this->className->__toString());
        }
        return 'class-string';
    }

    public function toPhpString(): string
    {
        return 'string';
    }

    public function accepts(Type $type): Trinary
    {
        if (!$type instanceof StringType) {
            return Trinary::false();
        }

        return Trinary::maybe();
    }
}
