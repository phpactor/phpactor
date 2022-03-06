<?php

namespace Phpactor\Completion\Bridge\WorseReflection\Formatter;

use Phpactor\Completion\Core\Formatter\Formatter;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\WorseReflection\Core\Reflection\ReflectionConstant;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnumCase;

class EnumCaseFormatter implements Formatter
{
    public function canFormat(object $object): bool
    {
        return $object instanceof ReflectionEnumCase;
    }

    public function format(ObjectFormatter $formatter, object $object): string
    {
        assert($object instanceof ReflectionConstant);

        if ($object->value() !== null) {
            return sprintf('case %s = %s', $object->name(), json_encode($object->value()));
        }

        return sprintf('case %s', $object->name());
    }
}
