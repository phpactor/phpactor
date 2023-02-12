<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Position;

interface ReflectionArgument
{
    public function guessName(): string;

    public function type(): Type;

    public function value(): mixed;

    public function position(): Position;
}
