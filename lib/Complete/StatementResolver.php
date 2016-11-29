<?php

declare(strict_types=1);

namespace Phpactor\Complete;

use PhpParser\Node\Stmt;
use PhpParser\Node;

class StatementResolver
{
    public function __invoke(
        Node $node,
        int $offset,
        Stmt\Namespace_ $namespace = null,
        Stmt\ClassMethod $classMethod = null,
        Stmt\Class_ $class = null
    ): Statement
    {
        if (false === isset($node->stmts)) {
            return new Statement($node, $namespace, $class, $classMethod);
        }

        if (get_class($node) === Stmt\Namespace_::class) {
            $namespace = $node;
        }

        if (get_class($node) === Stmt\Class_::class) {
            $class = $node;
        }

        if (get_class($node) === Stmt\ClassMethod::class) {
            $classMethod = $node;
        }

        foreach ($node->stmts as $stmt) {
            if ($offset >= $stmt->getAttribute('startFilePos')  && $offset <= $stmt->getAttribute('endFilePos')) {
                return $this->__invoke($stmt, $offset, $namespace, $classMethod, $class);
            }
        }

        return new Statement($node, $namespace, $class, $classMethod);
    }
}
