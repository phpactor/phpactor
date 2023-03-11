<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

use RuntimeException;

class UseStatement
{
    const TYPE_CLASS = 'class';
    const TYPE_FUNCTION = 'function';

    public function __construct(
        private Type $className,
        private ?string $alias = null,
        private ?string $type = self::TYPE_CLASS
    ) {
        if (!in_array($type, [ self::TYPE_CLASS, self::TYPE_FUNCTION ])) {
            throw new RuntimeException(sprintf(
                'Invalid use type'
            ));
        }
    }

    public function __toString(): string
    {
        if ($this->alias) {
            return (string) $this->className . ' as ' . $this->alias;
        }

        return (string) $this->className;
    }

    public static function fromNameAndAlias(string $type, string $alias = null): self
    {
        return new self(Type::fromString($type), $alias);
    }

    public static function fromNameAliasAndType(string $name, string $alias = null, string $type): self
    {
        return new self(Type::fromString($name), $alias, $type);
    }

    public static function fromType(string $type): self
    {
        return new self(Type::fromString($type));
    }

    public function hasAlias(): bool
    {
        return null !== $this->alias;
    }

    public function alias(): ?string
    {
        return $this->alias;
    }

    public function name(): Type
    {
        return $this->className;
    }

    public function type(): ?string
    {
        return $this->type;
    }
}
