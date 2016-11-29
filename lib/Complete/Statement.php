<?php

namespace Phpactor\Complete;

use PhpParser\Node\Stmt;
use PhpParser\Node;

class Statement
{
    private $node;
    private $namespace;
    private $classMethod;
    private $class;

    public function __construct(
        Node $node,
        Stmt\Namespace_ $namespace = null,
        Stmt\Class_ $class = null,
        Stmt\ClassMethod $classMethod = null
    )
    {
        $this->node = $node;
        $this->class = $class;
        $this->classMethod = $classMethod;
    }

    public function getNode(): Node
    {
        return $this->node;
    }

    public function hasClass()
    {
        return null !== $this->class;
    }

    public function getClassFqn()
    {
        $namespace = $this->namespace ? $this->namespace->name . '\\' : '';
        return $namespace .= $this->getClass()->name;
    }

    public function getClass(): Stmt\Class_
    {
        return $this->class;
    }

    public function getClassMethod(): Stmt\ClassMethod
    {
        return $this->classMethod;
    }
}
