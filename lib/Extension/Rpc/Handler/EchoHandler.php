<?php

namespace Phpactor\Extension\Rpc\Handler;

use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Response\EchoResponse;

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
        return EchoResponse::fromMessage($arguments['message']);
    }
}
