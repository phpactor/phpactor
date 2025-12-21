<?php

namespace Phpactor\Extension\Behat\Behat;

class Context
{
    public function __construct(
        private string $suite,
        private string $class
    ) {
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
