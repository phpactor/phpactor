<?php

namespace Phpactor\Complete;

use PhpParser\Node\Stmt;
use PhpParser\Node;

class ScopeResolver
{
    private $lastEndLine;

    public function __invoke(Node $node, int $lineNb, array $scopes = [  ], $namespace = null)
    {
        if (get_class($node) === Stmt\Namespace_::class) {
            $namespace = (string) $node->name;
        }

        if (empty($scopes)) {
            $scopes[] = new Scope($namespace, Scope::SCOPE_GLOBAL);
        }

        $nodeStartLine = $node->getAttribute('startLine');

        if ($lineNb > $this->lastEndLine && $lineNb < $nodeStartLine) {
            return array_pop($scopes);
        }

        if (get_class($node) === Stmt\Function_::class) {
            $scopes[] = new Scope($namespace, Scope::SCOPE_FUNCTION, $node);
        }

        if (get_class($node) === Stmt\Function_::class) {
            $scopes[] = new Scope($namespace, Scope::SCOPE_FUNCTION, $node);
        }

        if (get_class($node) === Stmt\Class_::class) {
            $scopes[] = new Scope($namespace, Scope::SCOPE_CLASS, $node);
            $this->classNode = $node;
        }

        if (get_class($node) === Stmt\ClassMethod::class) {
            $scopes[] = new Scope($namespace, Scope::SCOPE_CLASS_METHOD, $node);
            $this->classMethodNode = $node;
        }

        if ($nodeStartLine >= $lineNb) {
            return array_pop($scopes);
        }

        $this->lastEndLine = $node->getAttribute('endLine');

        if (false === isset($node->stmts)) {
            return;
        }

        foreach ($node->stmts as $stmt) {
            if (null !== $scope = $this->__invoke($stmt, $lineNb, $scopes, $namespace)) {
                return $scope;
            }
        }
    }
}
