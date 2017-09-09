<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\Rpc\Editor\EchoAction;

class EchoHandler implements Handler
{
    public function name(): string
    {
        return 'echo';
    }

    public function defaultParameters(): array
    {
        return [
            'message' => '',
        ];
    }

    public function handle(array $arguments)
    {
        return EchoAction::fromMessage($arguments['message']);
    }
}

