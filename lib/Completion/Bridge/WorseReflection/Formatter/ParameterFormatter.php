<?php

namespace Phpactor\Completion\Bridge\WorseReflection\Formatter;

use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;
use Phpactor\Completion\Core\Formatter\Formatter;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\WorseReflection\TypeUtil;

class ParameterFormatter implements Formatter
{
    public function canFormat(object $object): bool
    {
        return $object instanceof ReflectionParameter;
    }

    public function format(ObjectFormatter $formatter, object $object): string
    {
        assert($object instanceof ReflectionParameter);

        $paramInfo = [];
        $type = $object->inferredType();
        if (TypeUtil::isDefined($type)) {
            $paramInfo[] = $formatter->format($object->inferredType());
        }
        $paramInfo[] = '$' . $object->name();

        if ($object->default()->isDefined()) {
            $paramInfo[] = '= '. str_replace(PHP_EOL, '', var_export($object->default()->value(), true));
        }
        return implode(' ', $paramInfo);
    }
}
