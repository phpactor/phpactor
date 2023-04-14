<?php

namespace Phpactor\WorseReflection\Core\Type;

interface Literal
{
    public function value(): mixed;

    /**
     * @return static
     */
    public function withValue(mixed $value);
}
