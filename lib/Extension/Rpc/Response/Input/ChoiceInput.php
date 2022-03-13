<?php

namespace Phpactor\Extension\Rpc\Response\Input;

class ChoiceInput implements Input
{
    private string $name;

    private string $label;

    private ?string $default;

    private array $choices;

    private array $keyMap;

    private function __construct(
        string $name,
        string $label,
        array $choices,
        ?string $default = null,
        array $keyMap = []
    ) {
        $this->name = $name;
        $this->label = $label;
        $this->default = $default;
        $this->choices = $choices;
        $this->keyMap = $keyMap;
    }

    public static function fromNameLabelChoicesAndDefault(string $name, string $label, array $choices, string $default = null): self
    {
        return new static($name, $label, $choices, $default);
    }

    public static function fromNameLabelChoices(string $name, string $label, array $choices): self
    {
        return new static($name, $label, $choices);
    }

    public function withKeys(array $keyMap): self
    {
        return new self($this->name, $this->label, $this->choices, $this->default, $keyMap);
    }

    public function type(): string
    {
        return 'choice';
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

    public function choices(): array
    {
        return $this->choices;
    }

    public function parameters(): array
    {
        return [
            'default' => $this->default,
            'label' => $this->label,
            'choices' => $this->choices,
            'keyMap' => $this->keyMap,
        ];
    }
}
