<?php

namespace Phpactor\Extension\LanguageServer\Server;

use Phpactor\Extension\LanguageServer\Exception\UnknownMethod;

class MethodRegistry
{
    /**
     * @var array
     */
    private $methods = [];

    public function __construct(array $methods)
    {
        foreach ($methods as $method) {
            $this->add($method);
        }
    }

    public function get(string $methodName): Method
    {
        if (!isset($this->methods[$methodName])) {
            throw new UnknownMethod(
                $methodName,
                array_keys($this->methods)
            );
        }

        return $this->methods[$methodName];
    }

    private function add(Method $method)
    {
        $this->methods[$method->name()] = $method;
    }
}
