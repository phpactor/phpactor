<?php

namespace Phpactor\Complete;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeAbstract;

class Scope
{
    const SCOPE_GLOBAL = 'global';
    const SCOPE_CLASS = 'class';
    const SCOPE_CLASS_METHOD = 'class_method';
    const SCOPE_FUNCTION = 'function';

    private $scope;
    private $contextNode;
    private $namespace;
    private $nodes;

    public function __construct($namespace = null, string $scope, Node $contextNode = null)
    {
        $this->contextNode = $contextNode;
        $this->scope = $scope;
        $this->namespace = $namespace;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function hasContextNode(): bool
    {
        return null !== $this->node;
    }

    public function getContextNode(): Stmt
    {
        return $this->node;
    }

    public function addNode(Node $node)
    {
        $this->nodes[] = $node;
    }

    public function getNode(): NodeAbstract
    {
        return end($this->nodes);
    }

    public function __toString()
    {
        return $this->scope;
    }
}
