<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\Position;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope as CoreReflectionScope;
use Phpactor\WorseReflection\Core\ServiceLocator;

abstract class AbstractReflectedNode
{
    public function position(): Position
    {
        return Position::fromStartAndEnd(
            $this->node()->getStartPosition(),
            $this->node()->getEndPosition()
        );
    }

    public function scope(): CoreReflectionScope
    {
        return new ReflectionScope($this->serviceLocator()->reflector(), $this->node());
    }

    abstract protected function node(): Node;

    abstract protected function serviceLocator(): ServiceLocator;
}
