<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

final class Type extends Prototype
{
    private bool $none = false;

    public function __construct(private ?string $type = null, private mixed $originalType = null)
    {
        parent::__construct();
    }


    public function __toString(): string
    {
        return $this->type ?? '';
    }

    public function originalType(): mixed
    {
        return $this->originalType;
    }

    public static function fromString(string $type): Type
    {
        return new self($type);
    }

    public static function none(): Type
    {
        $new = new self();
        $new->none = true;

        return $new;
    }

    public function namespace(): ?string
    {
        $type = $this->type;
        if (null === $type) {
            return null;
        }

        if (str_starts_with($type, '?')) {
            $type = substr($type, 1);
        }

        if (false === strrpos($type, '\\')) {
            return null;
        }

        return substr($type, 0, strrpos($type, '\\'));
    }

    public function notNone(): bool
    {
        return false === $this->none;
    }
}
