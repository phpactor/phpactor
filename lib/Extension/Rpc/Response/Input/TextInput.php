<?php

namespace Phpactor\Extension\Rpc\Response\Input;

class TextInput implements Input
{
    private function __construct(private string $name, private string $label, private ?string $default = null, private ?string $type = null)
    {
    }

    public static function fromNameLabelAndDefault(string $name, string $label, ?string $default = null, ?string $type = null)
    {
        return new self($name, $label, $default, $type);
    }

    public function type(): string
    {
        return 'text';
    }

    public function name(): string
    {
        return $this->name;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function default(): ?string
    {
        return $this->default;
    }

    public function parameters(): array
    {
        return [
            'default' => $this->default,
            'label' => $this->label,
            'type' => $this->type,
        ];
    }
}
