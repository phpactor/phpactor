<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

interface HasEmptyType
{
    public function emptyType(): Type;
}
