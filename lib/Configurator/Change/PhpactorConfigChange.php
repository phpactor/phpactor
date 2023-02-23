<?php

namespace Phpactor\Configurator\Change;

use Phpactor\Configurator\Change;

class PhpactorConfigChange implements Change
{
    /**
     * @var array<string,mixed> $keyValues
     */
    public function __construct(private string $description, private array $keyValues)
    {
    }

    public function describe(): string
    {
        return $this->description;
    }

    /**
     * @return array<string,mixed>
     */
    public function keyValues(): array
    {
        return $this->keyValues;
    }
}
