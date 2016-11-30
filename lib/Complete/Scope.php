<?php

namespace Phpactor\Complete;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeAbstract;
use PhpParser\Node\Name;

class Scope
{
    const SCOPE_GLOBAL = 'global';
    const SCOPE_CLASS = 'class';
    const SCOPE_CLASS_METHOD = 'class_method';
    const SCOPE_FUNCTION = 'function';

    private $namespace;
    private $scopeName;
    private $scopeNode;
    private $parentScope;

    private $nodes;

    public function __construct(
        $namespace = null,
        $scopeName,
        Node $scopeNode,
        Scope $parentScope = null
    )
    {
        $this->namespace = $namespace;
        $this->scopeName = $scopeName;
        $this->scopeNode = $scopeNode;
        $this->parentScope = $parentScope;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getScopeName(): string
    {
        return $this->scopeName;
    }

    public function getClassFqn()
    {
        if ($this->namespace) {
            return Name::concat($this->namespace, $this->getClassScope()->getScopeNode()->name);
        }

        return $this->getClassScope()->getScopeNode()->name;
    }

    public function getScopeNode()
    {
        return $this->scopeNode;
    }

    public function getNode(): NodeAbstract
    {
        return end($this->nodes);
    }

    public function addNode(Node $node)
    {
        $this->nodes[] = $node;
    }

    public function __toString()
    {
        return $this->scopeName;
    }

    private function getClassScope()
    {
        if ($this->scopeName === self::SCOPE_CLASS) {
            return $this;
        }

        if ($this->scopeName === self::SCOPE_CLASS_METHOD) {
            return $this->parentScope;
        }

        throw new \InvalidArgumentException(sprintf(
            'Cannot get class for non-class scope "%s"', $this->scopeName
        ));
    }
}
