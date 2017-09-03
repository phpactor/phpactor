<?php

namespace Phpactor\ContextAction;

use Phpactor\WorseReflection\Core\Reflection\Inference\Symbol;

class ActionRegistry
{
    private $actionsByType = [];

    public function __construct(array $actionsBySymbolType)
    {
        foreach ($actionsBySymbolType as $symbolType => $actions) {
            foreach ($actions as $actionName => $action) {
                $this->register($symbolType, $actionName, $action);
            }
        }
    }

    public function actionNames(string $symbolType): array
    {
        Symbol::assertValidSymbolType($symbolType);

        if (false === isset($this->actionsByType[$symbolType])) {
            return [];
        }

        return array_keys($this->actionsByType[$symbolType]);
    }

    public function action(string $symbolType, string $actionName): array
    {
        Symbol::assertValidSymbolType($symbolType);

        if (false === isset($this->actionsByType[$symbolType])) {
            throw new \OutOfBoundsException(sprintf(
                'No actions for symbol type "%s"', $symbolType
            ));
        }

        if (false === isset($this->actionsByType[$symbolType][$actionName])) {
            throw new \OutOfBoundsException(sprintf(
                'No action "%s" for symbol type "%s", available actions: "%s"',
                $actionName, $symbolType, implode('", "', array_keys($this->actionsByType[$symbolType]))
            ));
        }

        return $this->actionsByType[$symbolType][$actionName];
    }

    private function register(string $symbolType, string $actionName, Action $action)
    {
        Symbol::assertValidSymbolType($symbolType);

        if (!isset($this->actionsByType[$symbolType])) {
            $this->actionsByType[$symbolType] = [];
        }

        $this->actionsByType[$symbolType][$actionName] = $action;
    }
}
