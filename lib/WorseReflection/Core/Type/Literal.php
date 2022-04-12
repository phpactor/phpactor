<?php

namespace Phpactor\WorseReflection\Core\Type;

interface Literal
{
    /**
     * @return mixed
     */
    public function value();

    /**
     * @param mixed $value
     * @return static
     */
    public function withValue($value);
}
