<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Phpactor\WorseReflection\Core\ServiceLocator;

class ReflectionStaticMethodCall extends AbstractReflectionMethodCall
{
    public function __construct(
        ServiceLocator $locator,
        Frame $frame,
        ScopedPropertyAccessExpression $node
    ) {
        parent::__construct($locator, $frame, $node);
    }

    public function isStatic(): bool
    {
        return true;
    }

    public function nameRange(): ByteOffsetRange
    {
    }
}
