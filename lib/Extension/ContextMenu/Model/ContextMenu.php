<?php

namespace Phpactor\Extension\ContextMenu\Model;

use DTL\Invoke\Invoke;
use RuntimeException;

class ContextMenu
{
    private array $actions = [];

    public function __construct(
        array $actions,
        private array $contexts
    ) {
        foreach ($actions as $name => $action) {
            $this->actions[$name] = Invoke::new(Action::class, $action);
        }
        $this->validate();
    }

    public static function fromArray(array $array): self
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

    private function validate(): void
    {
        $missingActions = [];
        foreach ($this->contexts as $name => $actions) {
            $keys = [];
            foreach ($actions as $actionName) {
                if (!isset($this->actions[$actionName])) {
                    throw new RuntimeException(sprintf(
                        'Action "%s" used in context "%s" does not exist, known actions: "%s"',
                        $actionName,
                        $name,
                        implode('", "', array_keys($this->actions))
                    ));
                }

                $action = $this->actions[$actionName];

                if (isset($keys[$action->key()])) {
                    throw new RuntimeException(sprintf(
                        'Key "%s" in context "%s" mapped by action "%s" is already used by action "%s"',
                        $action->key(),
                        $name,
                        $actionName,
                        $keys[$action->key()]
                    ));
                }

                $keys[$action->key()] = $actionName;
            }
        }
    }
}
