<?php

namespace PhpActor\Knowledge\Reflection;

class ParamReflection
{
    private $name;

    public function __construct(
        string $name
    )
    {
        $this->name = $name;
    }

    public function getName() 
    {
        return $this->name;
    }
}
