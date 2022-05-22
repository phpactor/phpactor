<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Reflection\OldCollection\ReflectionEnumCaseCollection;
use Phpactor\WorseReflection\Core\Reflection\OldCollection\ReflectionPropertyCollection;

interface ReflectionEnum extends ReflectionClassLike
{
    public function docblock(): DocBlock;

    public function properties(): ReflectionPropertyCollection;

    public function cases(): ReflectionEnumCaseCollection;
}
