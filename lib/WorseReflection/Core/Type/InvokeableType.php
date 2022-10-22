<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

interface InvokeableType
{
    /**
     * @return Type[]
     */
    public function arguments(): array;

    public function returnType(): Type;
}
