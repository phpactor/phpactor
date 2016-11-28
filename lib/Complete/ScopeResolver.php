<?php

namespace Phpactor\Complete;

use PhpParser\Node\Stmt;
use PhpParser\Node;

class ScopeResolver
{
    const SCOPE_GLOBAL = 'global';
    const SCOPE_CLASS = 'class';
    const SCOPE_CLASS_METHOD = 'class_method';
    const SCOPE_FUNCTION = 'function';

    private $lastEndLine;

    private $classNode;
    private $classMethodNode;
    private $functionNode;

    public function __invoke(Node $node, int $lineNb, array $scopes = [ self::SCOPE_GLOBAL ])
    {
        $nodeStartLine = $node->getAttribute('startLine');

        if ($lineNb > $this->lastEndLine && $lineNb < $nodeStartLine) {
            return array_pop($scopes);
        }

        if (get_class($node) === Stmt\Function_::class) {
            $scopes[] = self::SCOPE_FUNCTION;
            $this->functionNode = $node;
        }

        if (get_class($node) === Stmt\Class_::class) {
            $scopes[]= self::SCOPE_CLASS;
            $this->classNode = $node;
        }

        if (get_class($node) === Stmt\ClassMethod::class) {
            $scopes[] = self::SCOPE_CLASS_METHOD;
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
            if (null !== $scope = $this->__invoke($stmt, $lineNb, $scopes)) {
                return $scope;
            }
        }
    }

    public function getClassNode() 
    {
        return $this->classNode;
    }

    public function getFunctionNode() 
    {
        return $this->functionNode;
    }

    public function getClassMethodNode() 
    {
        return $this->classMethodNode;
    }
    
    
    
}
