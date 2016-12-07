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
use Phpactor\Complete\Suggestion;
use PhpParser\Node\Stmt\ClassMethod;

class VariableProvider implements ProviderInterface
{
    /**
     * @var Reflector
     */
    private $reflector;

    public function __construct(Reflector $reflector)
    {
        $this->reflector = $reflector;
    }

    public function canProvideFor(Scope $scope): bool
    {
        $node = $scope->getNode();

        return $node instanceof Variable || $node instanceof ClassMethod;
    }

    public function provide(Scope $scope, Suggestions $suggestions)
    {
        if (Scope::SCOPE_CLASS_METHOD === (string) $scope) {
            $this->getClassMethodVars($scope, $suggestions);
        }

        $this->provideSuperGlobals($suggestions);

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
            $suggestions->add(Suggestion::create($superGlobal, Suggestion::TYPE_VARIABLE, '*superglobal*'));
        }
    }

    private function getClassMethodVars(Scope $scope, Suggestions $suggestions)
    {
        $suggestions->add(Suggestion::create('$this', Suggestion::TYPE_VARIABLE, $scope->getClassFqn()));
        $suggestions->add(Suggestion::create('self', Suggestion::TYPE_VARIABLE, $scope->getClassFqn()));

        $reflection = $this->reflector->reflect($scope->getClassFqn());
        $method = $reflection->getMethod($scope->getScopeNode()->name);

        foreach ($method->getVariables() as $variable) {
            $suggestions->add(Suggestion::create(
                '$' . $variable->getName(),
                Suggestion::TYPE_VARIABLE,
                (string) $variable->getTypeObject()
            ));
        }
    }
}
