<?php

namespace Phpactor\Extension\Rpc;

class Dialog
{
    private $inputs = [];

    public function input(string $name, string $inputType, array $arguments)
    {
        $this->inputs[$name] = [
            $inputType, $arguments
        ];
    }
}
