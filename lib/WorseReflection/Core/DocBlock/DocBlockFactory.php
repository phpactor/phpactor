<?php

namespace Phpactor\WorseReflection\Core\DocBlock;

use Phpactor\WorseReflection\Core\TypeResolver;

interface DocBlockFactory
{
    public function create(string $docblock): DocBlock;
}
