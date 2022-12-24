<?php

namespace Phpactor\DocblockParser\Ast;

use Generator;
use Traversable;

abstract class Node implements Element
{
    protected const CHILD_NAMES = [
    ];

    public function toString(): string
    {
        $out = str_repeat(' ', $this->length());
        ;
        $start = $this->start();
        foreach ($this->tokens() as $token) {
            $out = substr_replace($out, $token->value, $token->start() - $start, $token->length());
        }

        return $out;
    }

    /**
     * @return Generator<Token>
     */
    public function tokens(): Generator
    {
        yield from $this->findTokens($this->children());
    }

    /**
     * Return the short name of the node class (e.g. ParamTag)
     */
    public function shortName(): string
    {
        return substr(get_class($this), strrpos(get_class($this), '\\') + 1);
    }

    /**
     * @return Generator<Element>
     */
    public function selfAndDescendantElements(): Generator
    {
        yield $this;
        yield from $this->traverseNodes($this->children());
    }

    /**
     * @template T of Element
     * @param class-string<T> $elementFqn
     * @return ($elementFqn is null ? Generator<Element> : Generator<T>)
     */
    public function descendantElements(?string $elementFqn = null): Generator
    {
        if (null === $elementFqn) {
            yield from $this->traverseNodes($this->children());
            return;
        }

        foreach ($this->traverseNodes($this->children()) as $element) {
            if ($element instanceof $elementFqn) {
                yield $element;
            }
        }
    }

    /**
     * @template T of Element
     * @param class-string<T> $elementFqn
     */
    public function hasDescendant(string $elementFqn): bool
    {
        foreach ($this->descendantElements($elementFqn) as $element) {
            return true;
        }

        return false;
    }

    /**
     * @template T of Element
     * @param class-string<T> $elementFqn
     * @return T|null
     */
    public function firstDescendant(string $elementFqn): ?Element
    {
        foreach ($this->descendantElements($elementFqn) as $element) {
            /** @phpstan-ignore-next-line */
            return $element;
        }

        return null;
    }

    /**
     * @param class-string<Element> $elementFqn
     * @return Generator<Element>
     */
    public function children(?string $elementFqn = null): Generator
    {
        if (!$elementFqn) {
            foreach (static::CHILD_NAMES as $name) {
                $child = $this->$name;
                if (null !== $child) {
                    yield $child;
                }
            }

            return;
        }

        foreach (static::CHILD_NAMES as $name) {
            $child = $this->$name;
            if ($child instanceof $elementFqn) {
                yield $child;
            }
        }
    }

    /**
     * Return the bytes offset for the start of this node.
     */
    public function start(): int
    {
        return $this->startOf($this->children());
    }

    /**
     * Return the bytes offset for the end of this node.
     */
    public function end(): int
    {
        return $this->endOf(array_reverse(iterator_to_array($this->children(), false)));
    }

    public function hasChild(string $elementFqn): bool
    {
        foreach ($this->children() as $child) {
            if ($child instanceof $elementFqn) {
                return true;
            }
        }

        return false;
    }

    public function length(): int
    {
        return $this->end() - $this->start();
    }

    /**
     * @param iterable<Element|array<Element>> $nodes
     *
     * @return Generator<Element>
     */
    private function traverseNodes(iterable $nodes): Generator
    {
        $result = [];
        foreach ($nodes as $child) {
            if (is_iterable($child)) {
                yield from $this->traverseNodes($child);
                continue;
            }

            if ($child instanceof Node) {
                yield from $child->selfAndDescendantElements();
                continue;
            }

            if ($child instanceof Token) {
                yield $child;
                continue;
            }
        }
    }

    /**
     * @param iterable<null|Element|array<Element>> $elements
     */
    private function endOf(iterable $elements): int
    {
        foreach ($elements as $element) {
            if (null === $element) {
                continue;
            }

            if (is_array($element)) {
                return $this->endOf(array_reverse($element));
            }

            if ($element instanceof Traversable) {
                return $this->endOf(array_reverse(iterator_to_array($element)));
            }

            return $element->end();
        }

        return 0;
    }

    /**
     * @param iterable<Element|array<Element>> $elements
     */
    private function startOf(iterable $elements): int
    {
        foreach ($elements as $element) {
            if ($element instanceof Element) {
                return $element->start();
            }
            if (is_iterable($element)) {
                return $this->startOf($element);
            }
        }

        return 0;
    }

    /**
     * @return Generator<Token>
     * @param iterable<Element|array<Element>> $nodes
     */
    private function findTokens(iterable $nodes): Generator
    {
        foreach ($nodes as $node) {
            if ($node instanceof Token) {
                yield $node;
                continue;
            }

            if ($node instanceof Node) {
                yield from $node->tokens();
            }

            if (is_iterable($node)) {
                yield from $this->findTokens($node);
            }
        }
    }
}
