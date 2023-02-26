<?php

namespace Phpactor\Configurator\Adapter\Phpactor;

use Closure;
use Phpactor\Configurator\Model\Change;

class PhpactorConfigChange implements Change
{
    /**
     * @param Closure(bool):array<string,mixed> $keyValues
     */
    public function __construct(private string $prompt, private Closure $keyValues)
    {
    }

    public function prompt(): string
    {
        return $this->prompt;
    }

    /**
     * @return array<string,Closure<bool>:array<string,mixed>>
     */
    public function keyValues(): Closure
    {
        return $this->keyValues;
    }
}
