<?php

namespace Phpactor\Extension\Behat\Behat;

class Context
{
    /**
     * @var string
     */
    private $suite;
    /**
     * @var string
     */
    private $class;

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
