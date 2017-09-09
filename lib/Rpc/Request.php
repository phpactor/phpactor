<?php

namespace Phpactor\Rpc;

use Phpactor\Rpc\ActionRequest;

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

    public static function fromArray(array $requestConfig)
    {
        $validKeys = [ 'actions'];
        if ($diff = array_diff(array_keys($requestConfig), $validKeys)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid keys "%s", valid keys: "%s"',
                implode('", "', $diff), implode('", "', $validKeys)
            ));
        }

        $actions = [];
        foreach ($requestConfig['actions'] as $action) {
            $actions[] = ActionRequest::fromArray($action);
        }

        return new self($actions);
    }

    /**
     * @return Action[]
     */
    public function actions(): array
    {
        return $this->actions;
    }

    private function addAction(ActionRequest $action)
    {
        $this->actions[] = $action;
    }
}
