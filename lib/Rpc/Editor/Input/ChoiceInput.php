<?php

namespace Phpactor\Rpc\Editor\Input;

class ChoiceInput implements Input
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $default;

    /**
     * @var array
     */
    private $choices;

    private function __construct(string $name, string $label, array $choices, string $default = null)
    {
        $this->name = $name;
        $this->label = $label;
        $this->default = $default;
        $this->choices = $choices;
    }

    public static function fromNameLabelChoicesAndDefault(string $name, string $label, array $choices, string $default = null)
    {
        return new self($name, $label, $choices, $default);
    }

    public static function fromNameLabelChoices(string $name, string $label, array $choices)
    {
        return new self($name, $label, $choices);
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
        ];
    }
}
