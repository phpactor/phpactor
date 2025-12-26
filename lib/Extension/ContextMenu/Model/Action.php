<?php

namespace Phpactor\Extension\ContextMenu\Model;

class Action
{
    public function __construct(
        private readonly string $action,
        private readonly ?string $key = null,
        private readonly array $parameters = []
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
