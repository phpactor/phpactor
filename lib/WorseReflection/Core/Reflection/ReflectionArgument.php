<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Type;

interface ReflectionArgument
{
    public function guessName(): string;

    public function type(): Type;

    public function value();

    public function position(): Position;
}
