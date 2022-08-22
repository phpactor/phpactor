<?php

namespace Phpactor\WorseReflection\Core\DocBlock;

use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;

interface DocBlockFactory
{
    public function create(string $docblock, ReflectionScope $scope): DocBlock;
}
