<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

final class Type extends Prototype
{
    private ?string $type;

    private bool $none = false;

    private bool $nullable = false;

    public function __construct(string $type = null, bool $nullable = false)
    {
        parent::__construct();
        $this->type = $type;
        $this->nullable = $nullable;
    }

    public function __toString()
    {
        return $this->type;
    }

    public static function fromString(string $string): Type
    {
        $nullable = 0 === strpos($string, '?');
        $type = $nullable ? substr($string, 1) : $string;

        return new self($type, $nullable);
    }

    public static function none(): Type
    {
        $new = new self();
        $new->none = true;

        return $new;
    }

    public function namespace(): ?string
    {
        if (null === $this->type) {
            return null;
        }

        if (false === strrpos($this->type, '\\')) {
            return null;
        }

        return substr($this->type, 0, strrpos($this->type, '\\'));
    }

    public function notNone(): bool
    {
        return false === $this->none;
    }

    public function nullable(): bool
    {
        return $this->nullable;
    }
}
