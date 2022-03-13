<?php

namespace Phpactor\Extension\Rpc\Response;

use Phpactor\Extension\Rpc\Response;

/**
 * Stack of actions.
 * Each action will be executed one-after-the-other in the editor.
 */
class CollectionResponse implements Response
{
    /**
     * @var Response[]
     */
    private array $actions;

    private function __construct(array $actions)
    {
        foreach ($actions as $action) {
            $this->add($action);
        }
    }

    public static function fromActions(array $actions)
    {
        return new self($actions);
    }

    public function name(): string
    {
        return 'collection';
    }

    public function parameters(): array
    {
        $actions = [];

        foreach ($this->actions as $action) {
            $actions[] = [
                'name' => $action->name(),
                'parameters' => $action->parameters()
            ];
        }

        return [
            'actions' => $actions
        ];
    }

    public function actions(): array
    {
        return $this->actions;
    }

    private function add(Response $action): void
    {
        $this->actions[] = $action;
    }
}
