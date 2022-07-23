<?php

namespace Phpactor\Extension\Behat\Behat;

class Context
{
    private string $suite;

    private string $class;

    public function __construct(string $suite, string $class)
    {
        $this->suite = $suite;
        $this->class = $class;
    }

    public function class(): string
    {
        return $this->class;
    }

    public function suite(): string
    {
        return $this->suite;
    }
}
