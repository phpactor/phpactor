<?php

/**
 * This file is part of the Aldi C&C Project and there for Aldi new Project IP.
 * The license terms agreed between ALDI and Spryker in the Framework Agreement
 * on Software and IT Services under § 10 shall apply.
 */

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionArgument;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\ServiceLocator;

/**
 * @extends \Phpactor\WorseReflection\Core\Reflection\Collection\AbstractReflectionCollection<\Phpactor\WorseReflection\Core\Reflection\ReflectionArgument>
 */
class ReflectionArgumentCollection extends AbstractReflectionCollection
{
    public static function fromArgumentListAndFrame(ServiceLocator $locator, ArgumentExpressionList $list, Frame $frame): self
    {
        $arguments = [];
        foreach ($list->getElements() as $element) {
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
     * @return array<string, \Phpactor\WorseReflection\Core\Reflection\ReflectionArgument>
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
