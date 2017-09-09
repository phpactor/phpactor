<?php

namespace Phpactor\Rpc;

use Phpactor\WorseReflection\Core\Reflection\Inference\Symbol;

class HandlerRegistry
{
    private $handlers = [];

    public function __construct(array $handlers)
    {
        foreach ($handlers as $handler) {
            $this->register($handler);
        }
    }

    public function get($handlerName): Handler
    {
        if (false === isset($this->handlers[$handlerName])) {
            throw new \InvalidArgumentException(sprintf(
                'No handler "%s", available handlers: "%s"',
                $handlerName, implode('", "', array_keys($this->handlers))
            ));
        }

        return $this->handlers[$handlerName];
    }

    private function register(Handler $handler)
    {
        $this->handlers[$handler->name()] = $handler;
    }
}
