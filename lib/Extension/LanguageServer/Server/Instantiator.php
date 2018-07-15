<?php

namespace Phpactor\Extension\LanguageServer\Server;

use ReflectionClass;
use ReflectionParameter;

class Instantiator
{
    public function instantiate(string $class, array $params)
    {
        $this->createInstance($class, $params);
    }

    private function createInstance($class, array $params)
    {
        $reflection = new ReflectionClass(get_class($object));
        
        if (!$reflection->hasMethod('__construct')) {
            return $reflection->newInstance();
        }
        
        $constructor = $reflection->getMethod('__construct');

        /** @var ReflectionParameter $parameter */
        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();
        }
    }
}
