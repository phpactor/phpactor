<?php

namespace Phpactor\Extension\LanguageServer\Server\Dispatcher;

use DTL\ArgumentResolver\ArgumentResolver;
use Phpactor\Extension\LanguageServer\Protocol\ResponseMessage;
use Phpactor\Extension\LanguageServer\Server\Dispatcher;
use Phpactor\Extension\LanguageServer\Server\MethodRegistry;

class InvokingDispatcher implements Dispatcher
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

    public function dispatch(string $method, array $arguments): ResponseMessage
    {
        $method = $this->registry->get($method);
        $arguments = $this->argumentResolver->resolveArguments(
            get_class($method),
            '__invoke',
            $arguments
        );

        $result = $method->__invoke(...$arguments);

        return new ResponseMessage(1, $result);
    }
}
