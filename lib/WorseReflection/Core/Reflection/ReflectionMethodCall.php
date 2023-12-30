<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionArgumentCollection;
use Phpactor\WorseReflection\Core\Type;

interface ReflectionMethodCall extends ReflectionNode
{
    public function position(): ByteOffsetRange;

    public function class(): ReflectionClassLike;

    public function name(): string;

    public function nameRange(): ByteOffsetRange;

    public function isStatic(): bool;

    public function arguments(): ReflectionArgumentCollection;

    public function inferredReturnType(): Type;
}
