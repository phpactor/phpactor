<?php

namespace Phpactor\Extension\Rpc\Response\Input;

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
     * @var string|null
     */
    private $default;

    /*
     * @var string
     */
    private $type;

    private function __construct(string $name, string $label, string $default = null, string $type = null)
    {
        $this->name = $name;
        $this->label = $label;
        $this->default = $default;
        $this->type = $type;
    }

    public static function fromNameLabelAndDefault(string $name, string $label, string $default = null, string $type = null)
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
