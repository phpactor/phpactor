<?php

namespace PhpActor\Knowledge\Reflection;

class ClassHierarchy
{
    private $classes = array();

    public function addClass(ClassReflection $class)
    {
        $this->classes[] = $class;
    }

    public function getTop()
    {
        if (!isset($this->classes[0])) {
            throw new \RuntimeException(
                'Class hierarchy has no class at index 0'
            );
        }

        return $this->classes[0];
    }

    public function getClasses()
    {
        return $this->classes;
    }

    /**
     * Return the methods contained in the class stack.
     */
    public function getMethods()
    {
        $methods = array();

        foreach ($this->classes as $class) {
            foreach ($class->getMethods() as $method) {
                $methods[$method->getName()] = $method;
            }
        }

        return $methods;
    }
}
