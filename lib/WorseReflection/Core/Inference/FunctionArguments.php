<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Countable;
use IteratorAggregate;
use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use PhpParser\NodeVisitor\NodeConnectingVisitor;
use Traversable;

/**
 * @implements IteratorAggregate<NodeContext>
 */
class FunctionArguments implements IteratorAggregate, Countable
{
    /**
     * @param NodeContext[] $arguments
     */
    private function __construct(private array $arguments)
    {
    }

    public function __toString(): string
    {
        return implode(', ', array_map(function (NodeContext $type) {
            return $type->type()->__toString();
        }, iterator_to_array($this->getIterator())));
    }

    public static function fromList(NodeContextResolver $resolver, Frame $frame, ?ArgumentExpressionList $list): self
    {
        if ($list === null) {
            return new self([]);
        }

        return new self(array_map(function (ArgumentExpression $node) use ($resolver, $frame): NodeContext {
            return $resolver->resolveNode($frame, $node);
        }, array_values(array_filter(
            $list->children,
            fn ($nodeOrToken) => $nodeOrToken instanceof ArgumentExpression
        ))));
    }

    public function at(int $index): NodeContext
    {
        if (!isset($this->arguments[$index])) {
            return NodeContext::none();
        }

        return $this->arguments[$index];
    }

    public function getIterator(): Traversable
    {
        foreach ($this->arguments as $argument) {
            yield $argument;
        }
    }

    public function count(): int
    {
        return count($this->arguments);
    }

    public function from(int $offset): self
    {
        $newArgs = [];
        foreach ($this->arguments as $argOffset => $argument) {
            if ($argOffset < $offset) {
                continue;
            }
            $newArgs[] = $argument;
        }

        return new self($newArgs);
    }
}
