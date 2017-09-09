<?php

namespace Phpactor\Rpc;

use Phpactor\Rpc\ActionRequest;
use Phpactor\Rpc\Action;

class Response
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

    public function toArray(): array
    {
        return [
            'actions' => array_map(function (Action  $action) {
                return [
                    'action' => $action->name(),
                    'parameters' => $action->parameters(),
                ];
            }, $this->actions)
        ];
    }

    private function addAction(Action $action)
    {
        $this->actions[] = $action;
    }
}
