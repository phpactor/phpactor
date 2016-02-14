<?php

namespace PhpActor\Knowledge\Reflection;

class ClassReflection
{
    private $fullyQualifiedName;
    private $shortName;
    private $doc;
    private $namespace;
    private $parent;
    private $file;
    private $methods = array();

    public function __construct(
        string $fullyQualifiedName,
        string $shortName,
        string $doc,
        string $namespace,
        string $file,
        string $parent = null
    )
    {
        $this->fullyQualifiedName = $fullyQualifiedName;
        $this->shortName = $shortName;
        $this->doc = $doc;
        $this->namespace = $namespace;
        $this->parent = $parent;
        $this->file = $file;
    }

    public function addMethod(MethodReflection $method)
    {
        $this->methods[$method->getName()] = $method;
    }

    public function getFullyQualifiedName(): string
    {
        return $this->fullyQualifiedName;
    }

    public function getShortName(): string
    {
        return $this->shortName;
    }

    public function getDoc(): string
    {
        return $this->doc;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }
}
