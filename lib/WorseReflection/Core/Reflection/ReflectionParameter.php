<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\DefaultValue;
use Phpactor\WorseReflection\Core\Types;

interface ReflectionParameter extends ReflectionNode
{
    public function position(): Position;

    public function name(): string;

    /**
     * @deprecated Use funtionLike()
     */
    public function method(): ReflectionFunctionLike;

    public function functionLike(): ReflectionFunctionLike;

    public function type(): Type;

    public function inferredTypes(): Types;

    public function default(): DefaultValue;

    public function byReference(): bool;

    public function isPromoted(): bool;
}
