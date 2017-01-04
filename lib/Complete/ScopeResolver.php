<?php

declare(strict_types=1);

namespace Phpactor\Complete;

use PhpParser\Node\Stmt;
use PhpParser\Node;

class ScopeResolver
{
    private $node;

    public function __invoke(Node $node, int $offset, $scope = null, $namespace = null)
    {
        // namespace has children which may contain the offset, but the statement
        // does not encompass the it.
        if (
            false === $node instanceof Stmt\Namespace_ && 
            ($offset < $node->getAttribute('startFilePos') || $offset > $node->getAttribute('endFilePos')) ) {
            return;
        }

        if ($node instanceof Stmt\Namespace_) {
            $namespace = (string) $node->name;
        }

        if (null === $scope) {
            $scope = new Scope($namespace, Scope::SCOPE_GLOBAL, $node);
        }
        
        if ($node instanceof Stmt\Function_) {
            $scope = new Scope($namespace, Scope::SCOPE_FUNCTION, $node, $scope);
        }

        if ($node instanceof Stmt\Class_) {
            $scope = new Scope($namespace, Scope::SCOPE_CLASS, $node, $scope);
        }

        if ($node instanceof Stmt\ClassMethod) {
            $scope = new Scope($namespace, Scope::SCOPE_CLASS_METHOD, $node, $scope);
        }

        $scope->addNode($node);

        if (false === isset($node->stmts)) {
            return $scope;
        }

        foreach ($node->stmts as $stmt) {
            if ($offset >= $stmt->getAttribute('startFilePos')  && $offset <= $stmt->getAttribute('endFilePos')) {
                return $this->__invoke($stmt, $offset, $scope, $namespace);
            }
        }

        return $scope;
    }
}
