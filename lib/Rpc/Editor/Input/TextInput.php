<?php

namespace Phpactor\Rpc\Editor\Input;

class TextInput implements Input
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

    private function __construct(string $name, string $label, string $default = null)
    {
        $this->name = $name;
        $this->label = $label;
        $this->default = $default;
    }

    public static function fromNameLabelAndDefault(string $name, string $label, string $default = null)
    {
        return new self($name, $label, $default);
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

    public function default(): string
    {
        return $this->default;
    }

    public function parameters(): array
    {
        return [
            'default' => $this->default,
            'label' => $this->label,
            'type' => 'file',
        ];
    }
}
