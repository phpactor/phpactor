<?php

namespace Phpactor\Extension\Rpc\Response;

use Phpactor\Extension\Rpc\Response;
use Exception;

class ErrorResponse implements Response
{
    private function __construct(private string $message, private string $details)
    {
    }

    public static function fromMessageAndDetails(string $message, string $details): ErrorResponse
    {
        return new self($message, $details);
    }

    public static function fromException(Exception $exception): ErrorResponse
    {
        return new self($exception->getMessage(), self::exceptionDetails($exception));
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

    public function message(): string
    {
        return $this->message;
    }

    public function details(): string
    {
        return $this->details;
    }

    private static function exceptionDetails(Exception $exception): string
    {
        $exceptions = [ $exception ];

        while ($previous = $exception->getPrevious()) {
            $exceptions[] = $previous;
            $exception = $previous;
        }

        $exceptions = array_reverse($exceptions);

        $details = [];
        foreach ($exceptions as $index => $exception) {
            $details[] = sprintf(
                "%s: %s\n%s",
                $index,
                $exception->getMessage(),
                $exception->getTraceAsString()
            );
        }

        return implode("\n" . "\n", $details);
    }
}
