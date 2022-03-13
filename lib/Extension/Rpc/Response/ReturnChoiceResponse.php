<?php

namespace Phpactor\Extension\Rpc\Response;

use Phpactor\Extension\Rpc\Response;

/**
 * Instruct the editor to return the options to the RPC caller.
 *
 * NOTE: No actions can be performed after this action.
 */
class ReturnChoiceResponse implements Response
{
    private array $options = [];

    private function __construct($options)
    {
        foreach ($options as $option) {
            $this->add($option);
        }
    }

    public function name(): string
    {
        return 'return_choice';
    }

    public function parameters(): array
    {
        $options = [];
        foreach ($this->options as $option) {
            $options[] = [
                'name' => $option->name(),
                'value' => $option->value(),
            ];
        }

        return [
            'choices' => $options
        ];
    }

    public static function fromOptions(array $options): ReturnChoiceResponse
    {
        return new self($options);
    }

    public function options()
    {
        return $this->options;
    }

    private function add(ReturnOption $option): void
    {
        $this->options[] = $option;
    }
}
