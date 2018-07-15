<?php

namespace Phpactor\Extension\LanguageServer\Server;

use DTL\ArgumentResolver\ArgumentResolver;
use Phpactor\Extension\LanguageServer\Server\MethodRegistry;

class Dispatcher
{
    /**
     * @var MethodRegistry
     */
    private $registry;

    /**
     * @var ArgumentResolver
     */
    private $argumentResolver;

    public function __construct(MethodRegistry $registry, ArgumentResolver $argumentResolver)
    {
        $this->registry = $registry;
        $this->argumentResolver = $argumentResolver;
    }

    public function dispatch(string $method, array $arguments)
    {
        $method = $this->registry->get($method);
        $arguments = $this->argumentResolver->resolveArguments(
            get_class($method),
            '__invoke',
            $arguments
        );

        return $method->__invoke(...$arguments);
    }
}
