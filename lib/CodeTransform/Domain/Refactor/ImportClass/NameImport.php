<?php

namespace Phpactor\CodeTransform\Domain\Refactor\ImportClass;

use Phpactor\Name\FullyQualifiedName;

class NameImport
{
    private const TYPE_CLASS = 'class';
    private const TYPE_FUNCTION = 'function';

    private function __construct(private string $type, private FullyQualifiedName $name, private ?string $alias = null)
    {
    }

    public static function forClass(string $name, ?string $alias = null): self
    {
        return new self(self::TYPE_CLASS, FullyQualifiedName::fromString($name), $alias);
    }

    public static function forFunction(string $name, ?string $alias = null): self
    {
        return new self(self::TYPE_FUNCTION, FullyQualifiedName::fromString($name), $alias);
    }

    public function isGlobalFunction(): bool
    {
        return $nameImport->isFunction() && $nameImport->name()->count() === 1;
    }

    public function alias(): ?string
    {
        return $this->alias;
    }

    public function name(): FullyQualifiedName
    {
        return $this->name;
    }

    public function isFunction(): bool
    {
        return $this->type === self::TYPE_FUNCTION;
    }

    public function isClass(): bool
    {
        return $this->type === self::TYPE_CLASS;
    }

    public function type(): string
    {
        return $this->type;
    }
}
