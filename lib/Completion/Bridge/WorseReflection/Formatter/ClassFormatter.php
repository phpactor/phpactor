<?php

namespace Phpactor\Completion\Bridge\WorseReflection\Formatter;

use Phpactor\Completion\Core\Formatter\Formatter;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;

class ClassFormatter implements Formatter
{
    public function canFormat(object $object): bool
    {
        return $object instanceof ReflectionClass;
    }

    public function format(ObjectFormatter $formatter, object $class): string
    {
        assert($class instanceof ReflectionClass);

        $info = [];

        if ($class->deprecation()->isDefined()) {
            $info [] = '⚠ ';
        }

        $info[] = $class->name();

        if ($class->methods()->has('__construct')) {
            $info[] = '(';
            $info[] = $formatter->format(
                $class->methods()
                ->get('__construct')
                ->parameters()
            );
            $info[] = ')';
        }

        return implode('', $info);
    }
}
