<?php

namespace Phpactor\Configurator\Change;

use Phpactor\Configurator\Model\Change;

class PhpactorConfigChange implements Change
{
    /**
     * @param array<string,mixed> $keyValues
     */
    public function __construct(private string $prompt, private array $keyValues)
    {
    }

    public function prompt(): string
    {
        return $this->prompt;
    }

    /**
     * @return array<string,mixed>
     */
    public function keyValues(): array
    {
        return $this->keyValues;
    }
}
