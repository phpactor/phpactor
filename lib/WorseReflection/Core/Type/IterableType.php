<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

interface IterableType
{
    public function iterableValueType(): Type;
    public function iterableKeyType(): Type;
}
