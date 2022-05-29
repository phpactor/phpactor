<?php

namespace Phpactor\WorseReflection\Core\DocBlock;

interface DocBlockFactory
{
    public function create(string $docblock): DocBlock;
}
