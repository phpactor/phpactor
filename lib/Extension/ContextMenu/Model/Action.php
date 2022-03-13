<?php

namespace Phpactor\Extension\ContextMenu\Model;

class Action
{
    private string $action;

    private ?string $key;

    private array $parameters;

    public function __construct(string $action, ?string $key = null, array $parameters = [])
    {
        $this->action = $action;
        $this->key = $key;
        $this->parameters = $parameters;
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
