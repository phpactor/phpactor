<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\NodeText;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;

interface ReflectionFunctionLike
{
    /**
     * @return ReflectionParameterCollection<ReflectionParameter>
     */
    public function parameters(): ReflectionParameterCollection;

    public function body(): NodeText;

    public function position(): ByteOffsetRange;

    public function frame(): Frame;

    public function docblock(): DocBlock;

    public function scope(): ReflectionScope;

    public function inferredType(): Type;

    public function type(): Type;
}
