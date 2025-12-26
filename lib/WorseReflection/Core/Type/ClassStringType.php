<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\ClassName;

class ClassStringType extends StringType
{
    public function __construct(private readonly ?ClassName $className = null)
    {
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
        if ($type instanceof ClassStringType) {
            // this is not really true - we should not accept a class-string<Foo> for class-string<Bar>
            // BUT also class-string<T> should accept class-string<Foo> as we
            // can't (easily) resolve the template var early.
            return Trinary::true();
        }

        if (!$type instanceof StringType) {
            return Trinary::false();
        }

        return Trinary::maybe();
    }

    public function className(): ?ClassName
    {
        return $this->className;
    }
}
