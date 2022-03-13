<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

use RuntimeException;

class UseStatement
{
    const TYPE_CLASS = 'class';
    const TYPE_FUNCTION = 'function';

    private Type $className;

    private ?string $alias;

    private ?string $type;

    public function __construct(Type $className, string $alias = null, string $type = self::TYPE_CLASS)
    {
        $this->className = $className;
        $this->alias = $alias;
        $this->type = $type;

        if (!in_array($type, [ self::TYPE_CLASS, self::TYPE_FUNCTION ])) {
            throw new RuntimeException(sprintf(
                'Invalid use type'
            ));
        }
    }

    public function __toString()
    {
        if ($this->alias) {
            return (string) $this->className . ' as ' . $this->alias;
        }

        return (string) $this->className;
    }

    public static function fromNameAndAlias(string $type, string $alias = null)
    {
        return new self(Type::fromString($type), $alias);
    }

    public static function fromNameAliasAndType(string $name, string $alias = null, string $type)
    {
        return new self(Type::fromString($name), $alias, $type);
    }

    public static function fromType(string $type)
    {
        return new self(Type::fromString($type));
    }

    public function hasAlias(): bool
    {
        return null !== $this->alias;
    }

    public function alias(): string
    {
        return $this->alias;
    }

    public function name(): Type
    {
        return $this->className;
    }

    public function type(): string
    {
        return $this->type;
    }
}
