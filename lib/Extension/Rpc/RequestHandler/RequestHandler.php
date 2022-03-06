<?php

namespace Phpactor\Extension\Rpc\RequestHandler;

use Phpactor\MapResolver\Resolver;
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

        $resolver = new Resolver();
        $parameters = $request->parameters();
        $defaults = $handler->configure($resolver);
        $arguments = $resolver->resolve($parameters);

        return $handler->handle($arguments);
    }
}
