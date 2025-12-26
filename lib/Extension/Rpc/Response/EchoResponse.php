<?php

namespace Phpactor\Extension\Rpc\Response;

use Phpactor\Extension\Rpc\Response;

class EchoResponse implements Response
{
    private function __construct(private readonly string $message)
    {
    }

    public static function fromMessage(string $message): self
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
