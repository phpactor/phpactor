<?php

namespace Phpactor\Completion\Bridge\WorseReflection\Formatter;

use Phpactor\Completion\Core\Formatter\Formatter;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;

class InterfaceFormatter implements Formatter
{
    public function canFormat(object $object): bool
    {
        return $object instanceof ReflectionInterface;
    }

    public function format(ObjectFormatter $formatter, object $object): string
    {
        assert($object instanceof ReflectionInterface);

        $info = [];

        if ($object->deprecation()->isDefined()) {
            $info [] = '⚠ ';
        }
        assert($object instanceof ReflectionInterface);
        $info[] = sprintf('%s (interface)', $object->name()->full());

        return implode('', $info);
    }
}
