<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionArgumentCollection;

interface ReflectionMethodCall
{
    public function position(): Position;

    public function class(): ReflectionClassLike;

    public function name(): string;

    public function isStatic(): bool;

    public function arguments(): ReflectionArgumentCollection;
}
