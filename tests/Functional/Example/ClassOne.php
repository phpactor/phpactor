<?php

namespace Phpactor\Tests\Functional\Example;

class ClassOne
{
    /**
     * @var ClassTwo
     */
    public $classTwo;

    /**
     * @var string
     */
    private $privateProperty;

    public static function createClassTwo(): ClassTwo
    {
    }
}
