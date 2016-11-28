<?php

namespace Phpactor\Complete;

use PhpParser\Node;
use PhpParser\Node\Expr;

class CompleteContext
{
    const SCOPE_GLOBAL = 'global';
    const SCOPE_CLASS = 'class';
    const SCOPE_FUNCTION = 'function';

    private $stmts;
    private $tokens;
    private $lineNb;

    private $parser;

    public function __construct(array $stmts, int $lineNb)
    {
        $this->stmts = $stmts;
        $this->lineNb = $lineNb;
    }

    public function getScope()
    {
        foreach ($this->stmts as $stmt) {
            $scope = (new ScopeResolver())->__invoke($stmt, $this->lineNb);

            if (null !== $scope) {
                break;
            }
        }

        return $scope;
    }

    public function getClassReflection()
    {

    }

    public function getStmts() 
    {
        return $this->stmts;
    }

    public function getToksn() 
    {
        return $this->toksn;
    }

    public function getLineNb() 
    {
        return $this->lineNb;
    }
}
