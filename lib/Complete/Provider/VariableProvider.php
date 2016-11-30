<?php

namespace Phpactor\Complete\Provider;

use PhpParser\Node;
use Phpactor\Complete\CompleteContext;
use PhpParser\Node\Stmt;
use Phpactor\Complete\Scope;
use BetterReflection\Reflector\Reflector;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use Phpactor\Complete\Suggestions;
use Phpactor\Complete\ProviderInterface;

class VariableProvider implements ProviderInterface
{
    private $reflector;

    public function __construct(Reflector $reflector)
    {
        $this->reflector = $reflector;
    }

    public function canProvideFor(CompleteContext $context): bool
    {
        return $context->getScope()->getNode() instanceof Variable;
    }

    public function provide(CompleteContext $context, Suggestions $suggestions)
    {
        $scope = $context->getScope();
        $this->provideSuperGlobals($suggestions);

        if (Scope::SCOPE_CLASS_METHOD === (string) $scope) {
            return $this->getClassMethodVars($context, $suggestions);
        }

        // TODO: Function scope
        // TODO: Closure scope
    }

    private function provideSuperGlobals(Suggestions $suggestions)
    {
        foreach ([
            '$GLOBALS',
            '$_SERVER',
            '$_GET',
            '$_POST',
            '$_FILES',
            '$_COOKIE',
            '$_SESSION',
            '$_REQUEST',
            '$_ENV'
        ] as $superGlobal) {
            $suggestions->add($superGlobal);
        }
    }

    private function getClassMethodVars($context, Suggestions $suggestions)
    {
        $suggestions->add('$this');

        $scope = $context->getScope();

        $reflection = $this->reflector->reflect($scope->getClassFqn());
        $method = $reflection->getMethod($scope->getScopeNode()->name);

        foreach ($method->getVariables() as $parameter) {
            $suggestions->add('$' . $parameter->getName());
        }
    }
}
