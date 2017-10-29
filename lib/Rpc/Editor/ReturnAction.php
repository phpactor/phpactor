<?php

namespace Phpactor\Rpc\Editor;

use Phpactor\Rpc\Response;

/**
 * Instruct the editor to return the value to the RPC caller.
 *
 * NOTE: No actions can be performed after this action.
 */
class ReturnAction implements Response
{
    /**
     * @var mixed
     */
    private $value;

    private function __construct($value)
    {
        $this->value = $value;
    }

    public function name(): string
    {
        return 'return';
    }

    public function parameters(): array
    {
        return [
            'value' => $this->value
        ];
    }

    public static function fromValue($value): ReturnAction
    {
        return new self($value);
    }

    public function value()
    {
        return $this->value;
    }
}
