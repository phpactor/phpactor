<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

final class ReturnType extends Prototype
{
    private $type;

    public function __construct(Type $type)
    {
        parent::__construct();
        $this->type = $type;
    }

    public function __toString()
    {
        return (string) $this->type;
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
        return $this->type->notNone();
    }
}
