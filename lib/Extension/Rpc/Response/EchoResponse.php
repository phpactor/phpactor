<?php

namespace Phpactor\Extension\Rpc\Response;

use Phpactor\Extension\Rpc\Response;

class EchoResponse implements Response
{
    private string $message;

    private function __construct(string $message)
    {
        $this->message = $message;
    }

    public static function fromMessage(string $message)
    {
        return new self($message);
    }

    public function name(): string
    {
        return 'echo';
    }

    public function parameters(): array
    {
        return [
            'message' => $this->message
        ];
    }

    public function message(): string
    {
        return $this->message;
    }
}
