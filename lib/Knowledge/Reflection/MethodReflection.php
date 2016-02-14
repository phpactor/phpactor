<?php

namespace PhpActor\Knowledge\Reflection;

class MethodReflection
{
    private $name;
    private $doc;
    private $params = array();

    public function __construct(
        string $name,
        string $doc
    )
    {
        $this->name = $name;
        $this->doc = $doc;
    }

    public function addParam(ParamReflection $paramReflection)
    {
        $this->params[$paramReflection->getName()] = $paramReflection;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getParams(): array
    {
        return $this->params;
    }
    
    public function getDoc()
    {
        return $this->doc;
    }
}
