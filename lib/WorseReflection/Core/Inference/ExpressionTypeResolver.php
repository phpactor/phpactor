<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\MissingType;

final class ExpressionTypeResolver
{
    /**
     * @return array<string,Type>
     */
    public function resolve(Frame $frame, Node $node): array
    {
        return [
        ];
    }
}
