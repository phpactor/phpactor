<?php

namespace Phpactor\WorseReflection\Core\FunctionStub;

use Closure;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FunctionStub;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Type;

class ClosureStub implements FunctionStub
{
    /**
     * @var Closure(NodeContextResolver, Frame, Type[]): NodeContext
     */
    private Closure $closure;

    /**
     * @param Closure(NodeContextResolver,Frame,Type[]): NodeContext $closure
     */
    public function __construct(Closure $closure)
    {
        $this->closure = $closure;
    }

    /**
     * {@inheritDoc}
     */
    public function invoke(NodeContextResolver $resolver, Frame $frame, array $types): NodeContext
    {
        $closure = $this->closure;
        return $closure($resolver, $frame, $types);
    }
}
