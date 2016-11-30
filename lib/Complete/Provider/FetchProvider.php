<?php

namespace Phpactor\Complete\Provider;

use Phpactor\Complete\CompleteContext;
use PhpParser\Node\Expr;
use BetterReflection\Reflector\Reflector;
use Phpactor\Complete\ProviderInterface;
use Phpactor\Complete\Suggestions;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\NodeAbstract;
use Phpactor\Complete\Scope;
use BetterReflection\Reflection\ReflectionClass;
use BetterReflection\Reflector\Exception\IdentifierNotFound;

class FetchProvider implements ProviderInterface
{
    public function __construct(Reflector $reflector)
    {
        $this->reflector = $reflector;
    }

    public function canProvideFor(CompleteContext $context): bool
    {
        // Currently only supporting class method fetch completion.
        if ((string) $context->getScope() !== Scope::SCOPE_CLASS_METHOD) {
            return false;
        }

        return $context->getScope()->getNode() instanceof Expr\PropertyFetch;
    }

    public function provide(CompleteContext $context, Suggestions $suggestions)
    {
        $scope = $context->getScope();
        $classReflection = $this->reflector->reflect($context->getScope()->getClassFqn());
        $localVariables = $classReflection->getMethod($scope->getScopeNode()->name)->getVariables();

        $fetches = $this->flattenFetch($context->getScope()->getNode());
        $initial = array_shift($fetches);

        foreach ($localVariables as $localVariable) {
            if ($initial !== $localVariable->getName()) {
                continue;
            }

            $type = $localVariable->getTypeObject();

            if ($type->isBuiltin()) {
                continue;
            }

            $reflection = null;
            try {
                $reflection = $this->reflector->reflect((string) $type);
            } catch (IdentifierNotFound $e) {
                // invalid class reference -- should collect errors here
            }
        }

        $this->resolveReflectionClass($reflection, $fetches, $suggestions);
    }

    private function resolveReflectionClass(ReflectionClass $reflection, array $fetches, Suggestions $suggestions)
    {
        // if there is only one fetch left, then it is the thing we are trying
        // to complete..
        if (1 === count($fetches)) {
            foreach ($reflection->getProperties() as $property) {
                $suggestions->add($property->getName());
            }

            foreach ($reflection->getMethods() as $method) {
                $suggestions->add($method->getName() . '(');
            }
        }

        $propName = array_shift($fetches);

        if (false === $reflection->hasProperty($propName)) {
            return;
        }

        $property = $reflection->getProperty($propName);
        $types = $property->getDocBlockTypeStrings();

        foreach ($types as $type) {
            try {
                $reflection = $this->reflector->reflect($type);
                return $this->resolveReflectionClass($reflection, $fetches, $suggestions);
            } catch (IdentifierNotFound $exception) {
            }
        }
    }

    private function flattenFetch(NodeAbstract $node)
    {
        $nodes = [];
        if ($node instanceof PropertyFetch && $node->var) {
            $nodes = $this->flattenFetch($node->var);
        }

        $nodes[] = $node->name;

        return $nodes;
    }
}
