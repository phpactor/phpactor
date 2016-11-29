<?php

namespace Phpactor\Complete\Provider;

use PhpParser\Node;
use Phpactor\Complete\CompleteContext;
use PhpParser\Node\Stmt;
use Phpactor\Complete\Scope;
use BetterReflection\Reflector\Reflector;

class VariableProvider
{
    private $reflector;

    public function __construct(Reflector $reflector)
    {
        $this->reflector = $reflector;
    }

    public function canProvideFor($context)
    {
    }

    public function provide(CompleteContext $context)
    {
        $statement = $context->getStatementToComplete();
        var_dump($statement);
        if (Scope::SCOPE_CLASS_METHOD === (string) $context->getScope()) {
            return $this->getClassMethodVars($context);
        }

        return [];
    }

    private function getClassMethodVars($context)
    {
        $suggestions = [
            '$this',
        ];

        $statement = $context->getStatementToComplete();

        $reflection = $this->reflector->reflect($statement->getClassFqn());
        $method = $reflection->getMethod($statement->getClassMethod()->name);
        foreach ($method->getVariables() as $parameter) {
            $suggestions[] = '$' . $parameter->getName();
        }

        return $suggestions;
    }
}
