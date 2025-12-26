<?php

namespace Phpactor\Extension\Rpc\Response\Input;

class ConfirmInput implements Input
{
    private function __construct(
        private readonly string $name,
        private readonly string $label
    ) {
    }

    public static function fromNameAndLabel(string $name, string $label)
    {
        return new self($name, $label);
    }

    public function type(): string
    {
        return 'confirm';
    }

    public function name(): string
    {
        return $this->name;
    }

    public function parameters(): array
    {
        return [
            'label' => $this->label
        ];
    }
}
