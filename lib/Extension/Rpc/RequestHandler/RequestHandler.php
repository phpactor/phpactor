<?php

namespace Phpactor\Extension\Rpc\RequestHandler;

use Phpactor\Extension\Rpc\HandlerRegistry;
use Phpactor\Extension\Rpc\RequestHandler as CoreRequestHandler;
use Phpactor\Extension\Rpc\Request;
use Phpactor\Extension\Rpc\Response;

class RequestHandler implements CoreRequestHandler
{
    /**
     * @var HandlerRegistry
     */
    private $registry;

    public function __construct(HandlerRegistry $registry)
    {
        $this->registry = $registry;
    }
    
    public function handle(Request $request): Response
    {
        $counterActions = [];
        $handler = $this->registry->get($request->name());

        $parameters = $request->parameters();
        $defaults = $handler->defaultParameters();

        if ($diff = array_diff(array_keys($parameters), array_keys($defaults))) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid arguments "%s" for handler "%s", valid arguments: "%s"',
                implode('", "', $diff),
                $handler->name(),
                implode('", "', array_keys($defaults))
            ));
        }

        return $handler->handle(array_merge($defaults, $parameters));
    }
}
