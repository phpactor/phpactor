<?php

namespace Phpactor\WorseReflection\Core\Virtual\Collection;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionParameter;

/**
 * @method \Phpactor\WorseReflection\Core\Virtual\ReflectionParameter get()
 * @method \Phpactor\WorseReflection\Core\Virtual\ReflectionParameter first()
 * @method \Phpactor\WorseReflection\Core\Virtual\ReflectionParameter last()
 */
class VirtualReflectionParameterCollection extends AbstractReflectionCollection implements ReflectionParameterCollection
{
    public static function fromReflectionParameters(array $reflectionParameters)
    {
        $parameters = [];
        foreach ($reflectionParameters as $reflectionParameter) {
            $parameters[$reflectionParameter->name()] = $reflectionParameter;
        }

        return new static($parameters);
    }

    public static function empty()
    {
        return new static([]);
    }

    public function add(VirtualReflectionParameter $virtualReflectionParameter): void
    {
        $this->items[$virtualReflectionParameter->name()] = $virtualReflectionParameter;
    }
    
    public function notPromoted(): ReflectionParameterCollection
    {
        return $this;
    }
    
    public function promoted(): ReflectionParameterCollection
    {
        return new self([]);
    }

    protected function collectionType(): string
    {
        return ReflectionParameterCollection::class;
    }
}
