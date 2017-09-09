<?php

namespace Phpactor\Rpc;

use Phpactor\WorseReflection\Core\Reflection\Inference\Symbol;

class ActionRegistry
{
    private $actions = [];

    public function __construct(array $actions)
    {
        foreach ($actions as $action) {
            $this->register($action);
        }
    }

    public function get($actionName): Action
    {
        if (false === isset($this->actions[$actionName])) {
            throw new \InvalidArgumentException(sprintf(
                'No action "%s", available actions: "%s"',
                $actionName, implode('", "', array_keys($this->actions))
            ));
        }

        return $this->actions[$actionName];
    }

    private function register(Action $action)
    {
        $this->actions[$action->name()] = $action;
    }
}
