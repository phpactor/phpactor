<?php

namespace Phpactor\Complete;

use PhpParser\Node;

class Scope
{
    const SCOPE_GLOBAL = 'global';
    const SCOPE_CLASS = 'class';
    const SCOPE_CLASS_METHOD = 'class_method';
    const SCOPE_FUNCTION = 'function';

    private $scope;
    private $node;
    private $namespace;

    public function __construct(string $namespace = null, string $scope, Node $node = null)
    {
        $this->node = $node;
        $this->scope = $scope;
        $this->namespace = $namespace;
    }

    public function getScope() 
    {
        return $this->scope;
    }

    public function hasNode()
    {
        return null !== $this->node;
    }

    public function getNode(): Node
    {
        return $this->node;
    }

    public function __toString()
    {
        return $this->scope;
    }

    public function getNamespace() 
    {
        return $this->namespace;
    }
}
