<?php

namespace Phpactor\Extension\ContextMenu\Model;

use DTL\Invoke\Invoke;
use RuntimeException;

class ContextMenu
{
    /**
     * @var array
     */
    private $actions = [];

    /**
     * @var array
     */
    private $contexts = [];

    public function __construct(array $actions, array $contexts)
    {
        foreach ($actions as $name => $action) {
            $this->actions[$name] = Invoke::new(Action::class, $action);
        }

        $this->contexts = $contexts;
    }

    public function fromArray(array $array)
    {
        return Invoke::new(self::class, $array);
    }

    public function hasContext(string $context): bool
    {
        return isset($this->contexts[$context]);
    }

    public function forContext(string $context): array
    {
        if (!isset($this->contexts[$context])) {
            throw new RuntimeException(sprintf(
                'Context "%s" does not exist',
                $context
            ));
        }

        return array_combine($this->contexts[$context], array_map(function (string $action) {
            return $this->getAction($action);
        }, $this->contexts[$context]));
    }

    private function getAction(string $name): Action
    {
        if (!isset($this->actions[$name])) {
            throw new RuntimeException(sprintf(
                'Action "%s" does not exist, known actions: "%s"',
                $name,
                implode('", "', array_keys($this->actions))
            ));
        }

        return $this->actions[$name];
    }
}
