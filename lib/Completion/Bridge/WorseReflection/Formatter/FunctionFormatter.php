<?php

namespace Phpactor\Completion\Bridge\WorseReflection\Formatter;

use Phpactor\Completion\Core\Formatter\Formatter;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;

class FunctionFormatter implements Formatter
{
    public function canFormat(object $object): bool
    {
        return $object instanceof ReflectionFunction;
    }

    public function format(ObjectFormatter $formatter, object $function): string
    {
        assert($function instanceof ReflectionFunction);

        $info = [
            $function->name()
        ];

        $paramInfos = [];

        foreach ($function->parameters() as $parameter) {
            $paramInfos[] = $formatter->format($parameter);
        }
        $info[] = '(' . implode(', ', $paramInfos) . ')';

        $returnType = $function->inferredType();

        if (($returnType->isDefined())) {
            $info[] = ': ' . $formatter->format($returnType);
        }

        return implode('', $info);
    }
}
