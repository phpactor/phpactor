<?php

namespace Phpactor\CodeBuilder\Domain\Builder;

class ConstructorBuilder extends MethodBuilder
{
    /**
     * @param mixed $originalType
     */
    public function returnType(string $returnType, $originalType = null): MethodBuilder
    {
        trigger_error('You can not have a return type on a constructor', E_USER_NOTICE);
        return $this;
    }

    public function parameter(string $name): ParameterBuilder|ConstructorParameterBuilder
    {
        if (isset($this->parameters[$name])) {
            return $this->parameters[$name];
        }

        $this->parameters[$name] = $builder = new ConstructorParameterBuilder($this, $name);

        return $builder;
    }
}
