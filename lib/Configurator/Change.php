<?php

namespace Phpactor\Configurator;

interface Change
{
    public function prompt(): string;
}
