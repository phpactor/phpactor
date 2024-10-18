<?php

namespace Phpactor\CodeTransform\Domain;

use Countable;
use IteratorAggregate;
use ArrayIterator;
use InvalidArgumentException;
use Traversable;

/**
 * @template T
 * @implements IteratorAggregate<string, T>
 */
abstract class AbstractCollection implements IteratorAggregate, Countable
{
    /**
     * @var array<string, T>
     */
    private array $elements = [];

    /**
     * @param T[] $elements
     */
    final public function __construct(array $elements)
    {
        foreach ($elements as $name => $element) {
            $type = $this->type();
            if (false === $element instanceof $type) {
                throw new InvalidArgumentException(sprintf(
                    'Collection element must be instanceof "%s"',
                    $type
                ));
            }
            $this->elements[(string)$name] = $element;
        }
    }

    /**
     * @return static<T>
     * @param T[] $elements
     */
    public static function fromArray(array $elements)
    {
        return new static($elements);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->elements);
    }

    public function names(): array
    {
        return array_keys($this->elements);
    }

    /**
     * @return T
     */
    public function get(string $name)
    {
        if (!isset($this->elements[$name])) {
            throw new InvalidArgumentException(sprintf(
                'Generator "%s" not known, known elements: "%s"',
                $name,
                implode('", "', array_keys($this->elements))
            ));
        }

        return $this->elements[$name];
    }

    public function count(): int
    {
        return count($this->elements);
    }

    abstract protected function type(): string;
}
