<?php

namespace Phpactor\Extension\ContextMenu\Model;

class Action
{
    public function __construct(
        private string $action,
        private ?string $key = null,
        private array $parameters = []
    ) {
    }

    public function action(): string
    {
        return $this->action;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }

    public function key(): ?string
    {
        return $this->key;
    }
}
