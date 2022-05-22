<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Microsoft\PhpParser\Node\Statement\FunctionDeclaration;
use Phpactor\WorseReflection\Core\Reflection\Collection\AbstractReflectionCollection;
use Phpactor\WorseReflection\Core\Reflection\OldCollection\ReflectionParameterCollection as PhpactorReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter as PhpactorReflectionParameter;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\Core\Reflection\OldCollection\ReflectionParameterCollection as CoreReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;

/**
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionParameter get()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionParameter first()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionParameter last()
 */
final class ReflectionParameterCollection extends AbstractReflectionCollection implements CoreReflectionParameterCollection
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

    public static function fromMethodDeclaration(ServiceLocator $serviceLocator, MethodDeclaration $method, ReflectionMethod $reflectionMethod)
    {
        $items = [];

        if ($method->parameters) {
            foreach ($method->parameters->getElements() as $parameter) {
                $items[$parameter->getName()] = new ReflectionParameter($serviceLocator, $reflectionMethod, $parameter);
            }
        }


        return new static($items);
    }

    public static function fromFunctionDeclaration(ServiceLocator $serviceLocator, FunctionDeclaration $functionDeclaration, ReflectionFunction $reflectionFunction)
    {
        $items = [];

        if ($functionDeclaration->parameters) {
            foreach ($functionDeclaration->parameters->getElements() as $parameter) {
                $items[$parameter->getName()] = new ReflectionParameter($serviceLocator, $reflectionFunction, $parameter);
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

    protected function collectionType(): string
    {
        return CoreReflectionParameterCollection::class;
    }
}
