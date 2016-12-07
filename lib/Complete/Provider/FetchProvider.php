<?php

namespace Phpactor\Complete\Provider;

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
use BetterReflection\TypesFinder\FindTypeFromAst;
use phpDocumentor\Reflection\Types\Object_;

class FetchProvider implements ProviderInterface
{
    /**
     * @var DocBlockFactory
     */
    private $docBlockFactory;

    public function __construct(Reflector $reflector)
    {
        $this->reflector = $reflector;
        $this->docBlockFactory = DocBlockFactory::createInstance();
    }

    public function canProvideFor(Scope $scope): bool
    {
        // Currently only supporting class method fetch completion.
        if ((string) $scope !== Scope::SCOPE_CLASS_METHOD) {
            return false;
        }

        return 
            $scope->getNode() instanceof Expr\ClassConstFetch ||
            $scope->getNode() instanceof Expr\PropertyFetch  || 
            $scope->getNode() instanceof Expr\MethodCall;
    }

    public function provide(Scope $scope, Suggestions $suggestions)
    {
        $node = $scope->getNode();
        if (
            $scope->getNode() instanceof PropertyFetch ||
            $scope->getNode() instanceof PropertyFetch
        ) {
            // knock off "completion" node
            $node = $scope->getNode()->var;
        }

        $classReflection = $this->resolveReflectionClass(
            $node,
            $scope
        );

        if (null === $classReflection) { 
            return;
        }

        // populate the suggestions with the classes members.
        $this->populateSuggestions($scope, $classReflection, $suggestions);
    }

    private function resolveReflectionClass(Expr $node, Scope $scope, ReflectionClass $reflectionClass = null)
    {
        if ($node instanceof Expr\PropertyFetch) {
            $reflectionClass = $this->resolveReflectionClass($node->var, $scope, $reflectionClass);
        }

        if ($node instanceof Expr\MethodCall) {
            $reflectionClass = $this->resolveReflectionClass($node->var, $scope, $reflectionClass);
        }

        // now we start from the base variable ...

        if ($node instanceof Expr\ClassConstFetch) {
            return $this->resolveReflectionFromConstantFetch($node, $scope, $reflectionClass);
        }

        if ($node instanceof Expr\Variable) {
            return $this->resolveReflectionFromLocalVariables($node->name, $scope);
        }

        if ($node instanceof Expr\StaticCall) {
            return $this->resolveReflectionFromStatic($node->name, $scope);
        }

        if (null === $reflectionClass) {
            return;
        }

        if ($resolvedReflection = $this->resolvePropertyReflection($reflectionClass, $node->name)) {
            return $resolvedReflection;
        }

        if ($resolvedReflection = $this->resolveMethodReflection($reflectionClass, $node->name)) {
            return $resolvedReflection;
        }

        // TODO: Should return null probably ...
        return $reflectionClass;
    }

    private function resolveReflectionFromConstantFetch(Expr\ClassConstFetch $node, Scope $scope)
    {
        // TODO: For god sakes generalize this...
        $reflectionClass = $this->reflector->reflect($scope->getClassFqn());
        $name = (string) $node->class;

        if ($name === 'self') {
            $reflectionClass = $this->reflector->reflect($scope->getClassFqn());
            return $reflectionClass;
        }

        // TODO: Refactor this
        $type = (new FindTypeFromAst())->__invoke(
            $name,
            $reflectionClass->getLocatedSource(),
            $reflectionClass->getNamespaceName()
        );

        if (false === $type instanceof Object_) {
            return;
        }

        return $this->reflector->reflect($type->getFqsen());

    }

    private function resolveReflectionFromLocalVariables(string $name, Scope $scope)
    {
        $reflectionClass = $this->reflector->reflect($scope->getClassFqn());

        if ($name === 'this') {
            return $reflectionClass;
        }

        $reflectionVariables = $reflectionClass->getMethod($scope->getScopeNode()->name)->getVariables();

        $reflection = $reflectionClass;
        foreach ($reflectionVariables as $reflectionVariable) {
            if ($name !== $reflectionVariable->getName()) {
                continue;
            }

            $type = $reflectionVariable->getTypeObject();

            // ignore primitives (i.e. non-objects)
            if ($type->isBuiltin()) {
                continue;
            }

            $resolvedReflection = $this->tryToReflectClass($type);

            if (null === $reflection) {
                continue;
            }

            return $resolvedReflection;
        }
    }

    private function resolvePropertyReflection(ReflectionClass $reflection, string $name)
    {
        if (false === $reflection->hasProperty($name)) {
            if ($parentClass = $reflection->getParentClass()) {
                return $this->resolvePropertyReflection($parentClass, $name);
            }

            return;
        }

        $property = $reflection->getProperty($name);

        if ($property->getDocComment()) {
            $types = $property->getDocBlockTypeStrings();
        } else {
            return;
        }

        foreach ($types as $type) {
            if (null === $reflection = $this->tryToReflectClass($type)) {
                continue;
            }

            return $reflection;
        }
    }

    private function resolveMethodReflection(ReflectionClass $reflectionClass, string $name)
    {
        if (false === $reflectionClass->hasMethod($name)) {
            if ($parentClass = $reflectionClass->getParentClass()) {
                return $this->resolveMethodReflection($parentClass, $name);
            }

            return;
        }

        $method = $reflectionClass->getMethod($name);

        return $this->tryToReflectClass($method->getReturnType());
    }

    private function tryToReflectClass($classFqn)
    {
        try {
            return $this->reflector->reflect($classFqn);
        } catch (IdentifierNotFound $exception) {
            // TODO: Log error here.
        }
    }

    private function populateSuggestions(Scope $scope, ReflectionClass $reflectionClass, Suggestions $suggestions)
    {
        $isStaticNode = $scope->isNodeStatic();

        foreach ($reflectionClass->getMethods() as $method) {
            if ($method->isStatic() && false === $isStaticNode) {
                continue;
            }

            if (false === $method->isStatic() && $isStaticNode) {
                continue;
            }

            $suggestions->add(Suggestion::create(
                $method->getName(),
                Suggestion::TYPE_METHOD,
                $this->formatMethodDoc($method)
            ));
        }

        $scopeReflection = $this->reflector->reflect($scope->getClassFqn());

        $classSameInstance = $reflectionClass->getName() == $scope->getClassFqn();

        // inherited properties currently not returned from BR:
        // https://github.com/Roave/BetterReflection/issues/231
        while ($reflectionClass) {
            foreach ($reflectionClass->getProperties() as $property) {
                $scopeIsInstance = $scopeReflection->isSubclassOf($reflectionClass->getName());
                $scopeIsSame = $scopeReflection->getName() === $reflectionClass->getName();

                if ($property->isPrivate() && false === $scopeIsSame) {
                    continue;
                }

                if ($property->isProtected() && (false === $scopeIsSame && false === $scopeIsInstance)) {
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

            $reflectionClass = $reflectionClass->getParentClass();
        }
    }

    /**
     * TODO: move this to formatting class
     */
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
}
