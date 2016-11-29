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
    private $offset;

    private $parser;

    public function __construct(array $stmts, int $offset)
    {
        $this->stmts = $stmts;
        $this->offset = $offset;
    }

    public function getScope()
    {
        foreach ($this->stmts as $stmt) {
            $scope = (new ScopeResolver())->__invoke($stmt, $this->offset);

            if (null !== $scope) {
                break;
            }
        }

        return $scope;
    }

    public function getStatementToComplete()
    {
        foreach ($this->stmts as $stmt) {
            $statement = (new StatementResolver())->__invoke($stmt, $this->offset);

            if (null !== $statement) {
                break;
            }
        }

        return $statement;
    }

    public function getStmts() 
    {
        return $this->stmts;
    }

    public function getToksn() 
    {
        return $this->toksn;
    }

    public function getOffset() 
    {
        return $this->offset;
    }
}
