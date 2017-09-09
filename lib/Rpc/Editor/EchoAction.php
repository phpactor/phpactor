<?php

namespace Phpactor\Rpc\Editor;

use Phpactor\Rpc\ActionRequest;
use Phpactor\Rpc\Action;

class EchoAction implements Action
{
    /**
     * @var string
     */
    private $message;

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
}

