<?php

namespace Phpactor\WorseReflection\Core\DocBlock;

use Phpactor\WorseReflection\Core\Type;

final class DocBlockTypeAssertion
{
    public function __construct(
        public string $variableName,
        public Type $type,
        public bool $negated
    ) {
    }
}
