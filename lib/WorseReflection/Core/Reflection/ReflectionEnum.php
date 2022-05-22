<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionEnumCaseCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;

interface ReflectionEnum extends ReflectionClassLike
{
    public function docblock(): DocBlock;

    public function properties(): ReflectionPropertyCollection;

    public function cases(): ReflectionEnumCaseCollection;
}
