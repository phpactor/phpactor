<?php

namespace Phpactor\Extension\Behat\Behat;

class Context
{
    public function __construct(
        private readonly string $suite,
        private readonly string $class
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
