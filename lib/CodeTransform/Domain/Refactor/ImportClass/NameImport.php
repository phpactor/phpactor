<?php

namespace Phpactor\CodeTransform\Domain\Refactor\ImportClass;

use Phpactor\Name\FullyQualifiedName;

class NameImport
{
    private const TYPE_CLASS = 'class';
    private const TYPE_FUNCTION = 'function';

    /**
     * @var string
     */
    private $type;

    /**
     * @var FullyQualifiedName
     */
    private $name;

    /**
     * @var string|null
     */
    private $alias;

    private function __construct(string $type, FullyQualifiedName $name, ?string $alias = null)
    {
        $this->type = $type;
        $this->name = $name;
        $this->alias = $alias;
    }

    public static function forClass(string $name, ?string $alias = null): self
    {
        return new self(self::TYPE_CLASS, FullyQualifiedName::fromString($name), $alias);
    }

    public static function forFunction(string $name, ?string $alias = null): self
    {
        return new self(self::TYPE_FUNCTION, FullyQualifiedName::fromString($name), $alias);
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
