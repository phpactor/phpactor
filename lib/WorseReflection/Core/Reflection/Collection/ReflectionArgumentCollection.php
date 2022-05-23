<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Phpactor\WorseReflection\Core\Reflection\ReflectionArgument as PhpactorReflectionArgument;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionArgument;

/**
 * @extends AbstractReflectionCollection<PhpactorReflectionArgument>
 */
class ReflectionArgumentCollection extends AbstractReflectionCollection
{
    public static function fromArgumentListAndFrame(ServiceLocator $locator, ArgumentExpressionList $list, Frame $frame): self
    {
        $arguments = [];
        foreach ($list->getElements() as $element) {
            $arguments[] = new ReflectionArgument($locator, $frame, $element);
        }

        return new self($arguments);
    }

    public function notPromoted(): self
    {
        return $this;
    }

    public function promoted(): self
    {
        return new self([]);
    }
}
