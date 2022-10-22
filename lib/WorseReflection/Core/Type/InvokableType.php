<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

interface InvokableType
{
    /**
     * @return Type[]
     */
    public function arguments(): array;

    public function returnType(): Type;
}
