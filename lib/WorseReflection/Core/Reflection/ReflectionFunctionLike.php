<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\NodeText;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Types;

interface ReflectionFunctionLike
{
    public function parameters(): ReflectionParameterCollection;

    public function body(): NodeText;

    public function position(): Position;

    public function frame(): Frame;

    public function docblock(): DocBlock;

    public function scope(): ReflectionScope;

    public function inferredTypes(): Types;

    public function type(): Type;
}
