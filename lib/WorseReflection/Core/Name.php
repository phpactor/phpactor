<?php

namespace Phpactor\WorseReflection\Core;

use InvalidArgumentException;

class Name
{
    protected $parts;

    private $wasFullyQualified;

    final public function __construct(array $parts, bool $wasFullyQualified)
    {
        $this->parts = $parts;
        $this->wasFullyQualified = $wasFullyQualified;
    }

    public function __toString()
    {
        return implode('\\', $this->parts);
    }

    public static function fromParts(array $parts)
    {
        return new static($parts, false);
    }

    public static function fromString(string $string): Name
    {
        $fullyQualified = 0 === strpos($string, '\\');
        $parts = explode('\\', trim($string, '\\'));

        return new static($parts, $fullyQualified);
    }

    /**
     * @param Name|string $value
     * @return static|Name
     */
    public static function fromUnknown($value): Name
    {
        if ($value instanceof Name) {
            return $value;
        }

        if (is_string($value)) {
            return static::fromString($value);
        }

        /** @phpstan-ignore-next-line */
        throw new InvalidArgumentException(sprintf(
            'Do not know how to create class from type "%s"',
            is_object($value) ? get_class($value) : gettype($value)
        ));
    }

    public function head(): self
    {
        return new self([ reset($this->parts) ], false);
    }

    public function tail(): self
    {
        $parts = $this->parts;
        array_shift($parts);
        return new self($parts, $this->wasFullyQualified);
    }

    public function namespace(): string
    {
        if (count($this->parts) === 1) {
            return '';
        }

        return implode('\\', array_slice($this->parts, 0, count($this->parts) - 1));
    }

    public function full(): string
    {
        return $this->__toString();
    }

    public function short(): string
    {
        return end($this->parts);
    }

    public function wasFullyQualified(): bool
    {
        return $this->wasFullyQualified;
    }

    /**
     * @return static
     */
    public function prepend($name)
    {
        $name = Name::fromUnknown($name);
        return self::fromString(join('\\', [(string) $name, $this->__toString()]));
    }

    public function isAncestorOrSame(Name $name): bool
    {
        $segment = array_slice($name->parts, 0, count($this->parts));
        return $segment === $this->parts;
    }

    public function substitute(Name $name, $alias)
    {
        $suffix = array_slice($this->parts, count($name->parts));
        return Name::fromParts(array_merge(
            [$alias],
            $suffix
        ));
    }

    public function count(): int
    {
        return count($this->parts);
    }
}
