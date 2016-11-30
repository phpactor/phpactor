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
use Phpactor\Complete\Suggestion;
use BetterReflection\Reflection\ReflectionMethod;
use BetterReflection\Reflection\ReflectionVariable;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types;

class FetchProvider implements ProviderInterface
{
    private $reflector;
    private $docBlockFactory;

    public function __construct(Reflector $reflector)
    {
        $this->reflector = $reflector;
        $this->docBlockFactory = DocBlockFactory::createInstance();
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
        $reflectionVariables = $classReflection->getMethod($scope->getScopeNode()->name)->getVariables();

        $fetches = $this->flattenFetch($context->getScope()->getNode());
        $initial = array_shift($fetches);

        $reflection = $classReflection;
        foreach ($reflectionVariables as $reflectionVariable) {
            if ($initial !== $reflectionVariable->getName()) {
                continue;
            }

            $type = $reflectionVariable->getTypeObject();

            // ignore primitives (i.e. non-objects)
            if ($type->isBuiltin()) {
                continue;
            }

            try {
                $reflection = $this->reflector->reflect($type);
                break;
            } catch (IdentifierNotFound $e) {
                // invalid class reference -- should collect errors here
            }
        }

        if (null === $reflection) {
            return;
        }

        $this->resolveReflectionClass($reflection, $fetches, $suggestions);
    }

    private function resolveReflectionClass(ReflectionClass $reflection, array $fetches, Suggestions $suggestions)
    {
        // if there is only one fetch left, then it is the thing we are trying
        // to complete..
        if (1 === count($fetches)) {
            foreach ($reflection->getProperties() as $property) {

                // TODO: Allow access when in scope.
                if ($property->isPrivate() || $property->isProtected()) {
                    continue;
                }

                $doc = null;
                if ($property->getDocComment()) {
                    $doc = $this->docBlockFactory->create($property->getDocComment())->getSummary();
                }

                $suggestions->add(Suggestion::create(
                    $property->getName(),
                    Suggestion::TYPE_PROPERTY,
                    $doc
                ));
            }

            foreach ($reflection->getMethods() as $method) {
                $suggestions->add(Suggestion::create(
                    $method->getName(),
                    Suggestion::TYPE_METHOD,
                    $this->formatMethodDoc($method)
                ));
            }
            return;
        }

        $propName = array_shift($fetches);

        if (false === $reflection->hasProperty($propName)) {
            return;
        }

        $property = $reflection->getProperty($propName);

        $types = [];
        if ($property->getDocComment()) {
            $types = $property->getDocBlockTypeStrings();
        }

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

    private function formatMethodDoc(ReflectionMethod $method)
    {
        $parts = [];
        foreach ($method->getParameters() as $parameter) {
            if ($parameter->getType()) {
                $type = $parameter->getType();
                $typeString = (string) $type;
                $parts[] = sprintf('%s $%s', $typeString, $parameter->getName());
                continue;
            }

            $parts[] = '$' . $parameter->getName();
        }


        $doc = $method->getName() . '(' . implode(', ', $parts) . '): ' . (string) $method->getReturnType();

        if ($method->getDocComment()) {
            $docObject = $this->docBlockFactory->create($method->getDocComment());

            return $doc . PHP_EOL . '    ' . $docObject->getSummary();
        }

        return $doc;
    }

    private function getVariableType(Scope $scope, string $name, ReflectionVariable $reflectionVariable)
    {
    }
}
