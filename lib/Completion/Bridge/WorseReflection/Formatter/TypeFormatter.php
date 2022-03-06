<?php

namespace Phpactor\Completion\Bridge\WorseReflection\Formatter;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\Completion\Core\Formatter\Formatter;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;

class TypeFormatter implements Formatter
{
    public function canFormat(object $object): bool
    {
        return $object instanceof Type;
    }

    public function format(ObjectFormatter $formatter, object $type): string
    {
        assert($type instanceof Type);

        if (false === $type->isDefined()) {
            return '<unknown>';
        }

        $shortName = $type->short();

        if ($type->arrayType()->isDefined()) {
            // generic
            if ($type->isClass()) {
                return sprintf('%s<%s>', $shortName, $type->arrayType()->short());
            }

            // array
            return sprintf('%s[]', $type->arrayType()->short());
        }

        return $shortName;
    }
}
