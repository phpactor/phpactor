<?php

namespace Phpactor\Configurator\Change;

use Phpactor\Configurator\Change;

class JsonFileChange implements Change
{
    public function __construct(string $path, 
    public function describe(): string
    {
    }

    public function apply(): void
    {
    }
}
