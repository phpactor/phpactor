<?php

namespace Phpactor\CodeTransform\Domain;

use Phpactor\WorseReflection\Core\Type;

interface DocBlockUpdater
{
    public function setReturnType(string $docblock, Type $type): string;

    public function setParam(string $string, string $string2, Type $type): string;
}
