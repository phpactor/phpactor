<?php

namespace Phpactor\Completion\Bridge\WorseReflection\Formatter;

use Phpactor\Completion\Core\Formatter\Formatter;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;

class MethodFormatter implements Formatter
{
    public function canFormat(object $object): bool
    {
        return $object instanceof ReflectionMethod;
    }

    public function format(ObjectFormatter $formatter, object $method): string
    {
        assert($method instanceof ReflectionMethod);

        $info = [];

        if ($method->deprecation()->isDefined()) {
            $info [] = '⚠ ';
        }

        $info[] = substr((string) $method->visibility(), 0, 3);
        $info[] = ' ';
        $info[] = $method->name();

        if ($method->isAbstract()) {
            array_unshift($info, 'abstract ');
        }

        $paramInfos = [];

        /** @var ReflectionParameter $parameter */
        foreach ($method->parameters() as $parameter) {
            $paramInfos[] = $formatter->format($parameter);
        }
        $info[] = '(' . implode(', ', $paramInfos) . ')';

        $returnType = $method->inferredType();

        if (($returnType->isDefined())) {
            $info[] = ': ' . $formatter->format($returnType);
        }

        return implode('', $info);
    }
}
