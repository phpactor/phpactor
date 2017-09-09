<?php

namespace Phpactor\Rpc;

class ActionRequest
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

    public static function fromArray(array $actionConfig)
    {
        $validKeys = [ 'action', 'parameters' ];
        if ($diff = array_diff(array_keys($actionConfig), $validKeys)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid request keys "%s", valid keys: "%s"',
                implode('", "', $diff), implode('", "', $validKeys)
            ));
        }

        return new self($actionConfig['action'], $actionConfig['parameters']);
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

