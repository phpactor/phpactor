<?php

namespace Phpactor\Extension\LanguageServer\Server;

class Dispatcher
{
    /**
     * @var LanguageServerHandler[]
     */
    private $handlers = [];

    public function __construct(array $handlers)
    {
        foreach ($handlers as $handler) {
            $this->handlers[$handler->name()] = $handler;
        }
    }

    public function dispatch(string $method, array $params)
    {
    }
}
