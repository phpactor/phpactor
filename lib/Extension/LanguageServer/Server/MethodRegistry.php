<?php

namespace Phpactor\Extension\LanguageServer\Server;

class MethodRegistry
{
    /**
     * @var array
     */
    private $methods;

    public function __construct(array $methods)
    {
        $this->methods = $methods;
    }

    public function get(string $methodName): Method
    {
        if (!isset($this->methods[$methodName])) {
            throw new RuntimeException(sprintf(
                'Unknown method "%s", known methods: "%s"',
                $methodName,
                implode('", "', $this->methods)
            ));
        }

        return $this->methods[$methodName];
    }
}
