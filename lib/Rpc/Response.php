<?php

namespace Phpactor\Rpc;

class Response
{
    private $actions = [];

    public static function fromActions(array $actions)
    {
        foreach ($actions as $action) {
            $this->addAction($action);
        }
    }

    public function toArray(): array
    {
        return [
            'actions' => array_map(function (Action  $action) {
                return $action->toArray();
            }, $this->actions)
        ];
    }

    private function addAction(Action $action)
    {
        $this->actions[] = $action;
    }
}
