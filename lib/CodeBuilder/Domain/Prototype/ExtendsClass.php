<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

final class ExtendsClass extends Prototype
{
    public function __construct(private Type $class)
    {
        parent::__construct();
    }

    public function __toString()
    {
        return (string) $this->class;
    }

    public static function fromString($string)
    {
        return new self(Type::fromString($string));
    }

    public static function none()
    {
        return new self(Type::none());
    }

    public function notNone(): bool
    {
        return $this->class->notNone();
    }
}
