<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Phpactor\WorseReflection\Core\Reflection\ReflectionArgument as PhpactorReflectionArgument;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Reflection\OldCollection\ReflectionParameterCollection as CoreReflectionParameterCollection;
use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionArgument;

/**
 * @extends AbstractReflectionCollection<ReflectionParameter>
 */
class ReflectionArgumentCollection extends AbstractReflectionCollection implements CoreReflectionParameterCollection
{
    public static function fromArgumentListAndFrame(ServiceLocator $locator, ArgumentExpressionList $list, Frame $frame): self
    {
        $arguments = [];
        foreach ($list->getElements() as $element) {
            $arguments[] = new ReflectionArgument($locator, $frame, $element);
        }

        return new self($arguments);
    }

    public function notPromoted(): CoreReflectionParameterCollection
    {
        return $this;
    }

    public function promoted(): CoreReflectionParameterCollection
    {
        return new self([]);
    }

    protected function collectionType(): string
    {
        return CoreReflectionParameterCollection::class;
    }
}
