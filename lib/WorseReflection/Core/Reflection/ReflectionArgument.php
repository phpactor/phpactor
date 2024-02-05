<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\TextDocument\ByteOffsetRange;

interface ReflectionArgument
{
    public function guessName(): string;

    public function type(): Type;

    public function value(): mixed;

    public function position(): ByteOffsetRange;

    public function nodeContext(): NodeContext;
}
