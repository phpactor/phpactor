<?php

namespace Phpactor;

use Phpactor\WorseReflection\Core\Reflection\ReflectionClass as Barbar;


class Test
{
    /**
     * @var string
     */
    private $ba;

    public function __construct(Barbar $class)
    {
        $this->ba = 'asd';
        $this->foobar($class);
    }

    private function foobar(ReflectionClass $class)
    {
    }
}
