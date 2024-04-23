<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
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
            if (!$element instanceof ArgumentExpression) {
                continue;
            }
            if ($element->name) {
                $key = $element->name->getText($element->getFileContents());
                $arguments[$key] = new ReflectionArgument($locator, $frame, $element);
                continue;
            }
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

    /**
     * @return array<string,PhpactorReflectionArgument>
     */
    public function named(): array
    {
        $arguments = [];
        $counters = [];
        foreach ($this as $argument) {
            $name = $argument->guessName();

            if (isset($arguments[$name])) {
                if (!isset($counters[$name])) {
                    $counters[$name] = 1;
                }
                $counters[$name]++;
                $name = $argument->guessName() . $counters[$name];
            }

            $arguments[$name] = $argument;
        }

        return $arguments;
    }
}
