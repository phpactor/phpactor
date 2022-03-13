<?php

namespace Phpactor\FilePathResolver\Expander;

use Closure;
use Phpactor\FilePathResolver\Expander;
use RuntimeException;

class CallbackExpander implements Expander
{
    private string $tokenName;

    private Closure $callback;

    public function __construct(string $tokenName, Closure $callback)
    {
        $this->tokenName = $tokenName;
        $this->callback = $callback;
    }

    public function tokenName(): string
    {
        return $this->tokenName;
    }

    public function replacementValue(): string
    {
        $closure = $this->callback;
        $return = $closure();

        if (!is_string($return)) {
            throw new RuntimeException(sprintf(
                'Closure in callback expander must return a string, got "%s"',
                gettype($return)
            ));
        }

        return $return;
    }
}
