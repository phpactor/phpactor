<?php

namespace Phpactor\Rpc;

final class Action
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var array
     */
    private $parameters;

    private function __construct(string $name, array $parameters)
    {
        $this->name = $name;
        $this->parameters = $parameters;
    }

    public static function fromNameAndParameters(string $name, array $parameters)
    {
        return new self($name, $parameters);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }
}

