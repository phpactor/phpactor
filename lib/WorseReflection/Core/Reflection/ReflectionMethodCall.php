<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionArgumentCollection;
use Phpactor\WorseReflection\Core\Type;

interface ReflectionMethodCall
{
    public function position(): Position;

    public function class(): ReflectionClassLike;

    public function name(): string;

    public function nameRange(): ByteOffsetRange;

    public function isStatic(): bool;

    public function arguments(): ReflectionArgumentCollection;

    public function inferredReturnType(): Type;
}
