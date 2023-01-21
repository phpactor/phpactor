<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Microsoft\PhpParser\Node\Statement\FunctionDeclaration;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection as PhpactorReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter as PhpactorReflectionParameter;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;

/**
 * @extends AbstractReflectionCollection<PhpactorReflectionParameter>
 */
final class ReflectionParameterCollection extends AbstractReflectionCollection
{
    /**
     * @param ReflectionParameter[] $reflectionParameters
     */
    public static function fromReflectionParameters(array $reflectionParameters): self
    {
        $parameters = [];
        foreach ($reflectionParameters as $reflectionParameter) {
            $parameters[$reflectionParameter->name()] = $reflectionParameter;
        }

        return new self($parameters);
    }

    public static function fromMethodDeclaration(ServiceLocator $serviceLocator, MethodDeclaration $method, ReflectionMethod $reflectionMethod): self
    {
        $items = [];

        /** @phpstan-ignore-next-line */
        if ($method->parameters) {
            $index = 0;
            foreach ($method->parameters->getElements() as $parameter) {
                $items[$parameter->getName()] = new ReflectionParameter(
                    $serviceLocator,
                    $reflectionMethod,
                    $parameter,
                    $index++
                );
            }
        }


        return new static($items);
    }

    public static function fromFunctionDeclaration(ServiceLocator $serviceLocator, FunctionDeclaration $functionDeclaration, ReflectionFunction $reflectionFunction): self
    {
        $items = [];

        /**
         * @phpstan-ignore-next-line
         */
        if ($functionDeclaration->parameters) {
            $index = 0;
            foreach ($functionDeclaration->parameters->getElements() as $parameter) {
                $items[$parameter->getName()] = new ReflectionParameter(
                    $serviceLocator,
                    $reflectionFunction,
                    $parameter,
                    $index++
                );
            }
        }


        return new static($items);
    }

    public function promoted(): PhpactorReflectionParameterCollection
    {
        return new self(array_filter($this->items, function (PhpactorReflectionParameter $parameter) {
            return $parameter->isPromoted();
        }));
    }

    public function notPromoted(): PhpactorReflectionParameterCollection
    {
        return new self(array_filter($this->items, function (PhpactorReflectionParameter $parameter) {
            return !$parameter->isPromoted();
        }));
    }

    public function add(PhpactorReflectionParameter $parameter): void
    {
        $this->items[$parameter->name()] = $parameter;
    }
}
