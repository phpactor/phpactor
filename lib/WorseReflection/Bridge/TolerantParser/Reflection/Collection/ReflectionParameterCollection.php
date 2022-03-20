<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection;

use Microsoft\PhpParser\Node\Statement\FunctionDeclaration;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection as PhpactorReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter as PhpactorReflectionParameter;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection as CoreReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;

/**
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionParameter get()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionParameter first()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionParameter last()
 */
class ReflectionParameterCollection extends AbstractReflectionCollection implements CoreReflectionParameterCollection
{
    public static function fromMethodDeclaration(ServiceLocator $serviceLocator, MethodDeclaration $method, ReflectionMethod $reflectionMethod)
    {
        $items = [];

        if ($method->parameters) {
            foreach ($method->parameters->getElements() as $parameter) {
                $items[$parameter->getName()] = new ReflectionParameter($serviceLocator, $reflectionMethod, $parameter);
            }
        }


        return new static($serviceLocator, $items);
    }

    public static function fromFunctionDeclaration(ServiceLocator $serviceLocator, FunctionDeclaration $functionDeclaration, ReflectionFunction $reflectionFunction)
    {
        $items = [];

        if ($functionDeclaration->parameters) {
            foreach ($functionDeclaration->parameters->getElements() as $parameter) {
                $items[$parameter->getName()] = new ReflectionParameter($serviceLocator, $reflectionFunction, $parameter);
            }
        }


        return new static($serviceLocator, $items);
    }

    public function promoted(): PhpactorReflectionParameterCollection
    {
        return new self($this->serviceLocator, array_filter($this->items, function (PhpactorReflectionParameter $parameter) {
            return $parameter->isPromoted();
        }));
    }

    public function notPromoted(): PhpactorReflectionParameterCollection
    {
        return new self($this->serviceLocator, array_filter($this->items, function (PhpactorReflectionParameter $parameter) {
            return !$parameter->isPromoted();
        }));
    }

    protected function collectionType(): string
    {
        return CoreReflectionParameterCollection::class;
    }
}
