<?php

namespace Phpactor\Rpc;

final class Request
{
    private $actions = [];

    private function __construct(array $actions)
    {
        foreach ($actions as $action) {
            $this->addAction($action);
        }
    }

    public static function fromActions(array $actions)
    {
        return new self($actions);
    }

    /**
     * @return Action[]
     */
    public function actions(): array
    {
        return $this->actions;
    }

    private function addAction(Action $action)
    {
        $this->actions[] = $action;
    }
}
