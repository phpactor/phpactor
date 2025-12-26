<?php declare(strict_types=1);

namespace Phpactor\FilePathResolver\Expander;

use Closure;
use Phpactor\FilePathResolver\Expander;

class CallbackExpander implements Expander
{
    /** @var Closure():string */
    private readonly Closure $callback;

    /**
     * @param Closure():string $callback
    */
    public function __construct(
        private readonly string $tokenName,
        Closure $callback
    ) {
        $this->callback = $callback;
    }

    public function tokenName(): string
    {
        return $this->tokenName;
    }

    public function replacementValue(): string
    {
        $closure = $this->callback;
        return $closure();
    }
}
