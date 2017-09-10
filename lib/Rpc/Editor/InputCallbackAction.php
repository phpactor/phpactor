<?php

namespace Phpactor\Rpc\Editor;

use Phpactor\Rpc\Action;
use Phpactor\Rpc\ActionRequest;

class InputCallbackAction implements Action
{
    /**
     * @var ActionRequest
     */
    private $callbackAction;

    /**
     * @var array
     */
    private $inputs;

    private function __construct(ActionRequest $callbackAction, array $inputs)
    {
        $this->callbackAction = $callbackAction;

        foreach ($inputs as $input) {
            $this->add($input);
        }
    }

    public static function fromCallbackAndInputs(ActionRequest $callbackAction, array $inputs)
    {
        return new self($callbackAction, $inputs);
    }

    public function name(): string
    {
        return 'input_callback';
    }

    public function parameters(): array
    {
        return [
            'inputs' => $this->inputs,
            'callback' => [
                'action' => $this->callbackAction->name(),
                'parameters' => $this->callbackAction->parameters()
            ],
        ];
    }

    private function add(Input $input)
    {
        $this->inputs[] = $input;
    }
}
