<?php

namespace Phpactor\Completion\Bridge\WorseReflection\Formatter;

use Phpactor\Completion\Core\Formatter\Formatter;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnumCase;

class EnumCaseFormatter implements Formatter
{
    public function canFormat(object $object): bool
    {
        return $object instanceof ReflectionEnumCase;
    }

    public function format(ObjectFormatter $formatter, object $object): string
    {
        assert($object instanceof ReflectionEnumCase);

        return sprintf('case %s', $object->name());
    }
}
