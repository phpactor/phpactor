<?php

namespace Phpactor\Rpc\Editor;

use Phpactor\Rpc\ActionRequest;
use Phpactor\Rpc\Action;

class ErrorAction implements Action
{
    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $details;

    private function __construct(string $message, string $details)
    {
        $this->message = $message;
        $this->details = $details;
    }

    public static function fromMessageAndDetails(string $message, string $details)
    {
        return new self($message, $details);
    }

    public function name(): string
    {
        return 'error';
    }

    public function parameters(): array
    {
        return [
            'message' => $this->message,
            'details' => $this->details
        ];
    }
}

