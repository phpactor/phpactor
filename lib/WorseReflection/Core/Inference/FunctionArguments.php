<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\MissingType;

class FunctionArguments
{
    /**
     * @var ArgumentExpression[]
     */
    private array $arguments;
    private NodeContextResolver $resolver;
    private Frame $frame;

    /**
     * @param ArgumentExpression[] $arguments
     */
    public function __construct(NodeContextResolver $resolver, Frame $frame, array $arguments)
    {
        $this->arguments = $arguments;
        $this->resolver = $resolver;
        $this->frame = $frame;
    }

    public static function fromList(NodeContextResolver $resolver, Frame $frame, ArgumentExpressionList $list): self
    {
        return new self($resolver, $frame, array_filter(
            $list->children,
            fn ($nodeOrToken) => $nodeOrToken instanceof ArgumentExpression
        ));
    }

    public function at(int $index): NodeContext
    {
        if (!isset($this->arguments[$index])) {
            return NodeContext::none();
        }

        return $this->resolver->resolveNode($this->frame, $this->arguments[$index]);

    }

}
