<?php

namespace Phpactor\Rpc;

use Phpactor\Rpc\HandlerRegistry;

class RequestHandler
{
    /**
     * @var HandlerRegistry
     */
    private $registry;

    public function __construct(HandlerRegistry $registry)
    {
        $this->registry = $registry;
    }
    
    public function handle(Request $request)
    {
        $counterActions = [];
        foreach ($request->actions() as $action) {
            $handler = $this->registry->get($action->name());

            $parameters = $action->parameters();
            $defaults = $handler->defaultParameters();

            if ($diff = array_diff(array_keys($parameters), array_keys($defaults))) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid arguments "%s" for handler "%s", valid arguments: "%s"',
                    implode('", "', $diff), $handler->name(), implode('", "', array_keys($defaults))
                ));
            }

            $counterActions[] = $handler->handle(array_merge($defaults, $parameters));
        }

        return Response::fromActions($counterActions);
    }
}
