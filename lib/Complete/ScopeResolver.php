<?php

declare(strict_types=1);

namespace Phpactor\Complete;

use PhpParser\Node\Stmt;
use PhpParser\Node;

class ScopeResolver
{
    public function __invoke(Node $node, int $offset, $scope = null, $namespace = null): Scope
    {
        if (get_class($node) === Stmt\Namespace_::class) {
            $namespace = (string) $node->name;
        }

        if (null === $scope) {
            $scope = new Scope($namespace, Scope::SCOPE_GLOBAL);
        }

        if (get_class($node) === Stmt\Function_::class) {
            $scope = new Scope($namespace, Scope::SCOPE_FUNCTION, $node);
        }

        if (get_class($node) === Stmt\Class_::class) {
            $scope = new Scope($namespace, Scope::SCOPE_CLASS, $node);
        }

        if (get_class($node) === Stmt\ClassMethod::class) {
            $scope = new Scope($namespace, Scope::SCOPE_CLASS_METHOD, $node);
        }

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
