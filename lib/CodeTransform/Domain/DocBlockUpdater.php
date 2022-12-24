<?php

namespace Phpactor\CodeTransform\Domain;

use Phpactor\WorseReflection\Core\Type;

interface DocBlockUpdater
{
    public function setReturnType(string $docblock, Type $type): string;
}
