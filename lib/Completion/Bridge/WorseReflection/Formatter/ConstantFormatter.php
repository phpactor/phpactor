<?php

namespace Phpactor\Completion\Bridge\WorseReflection\Formatter;

use Phpactor\Completion\Core\Formatter\Formatter;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\WorseReflection\Core\Reflection\ReflectionConstant;

class ConstantFormatter implements Formatter
{
    public function canFormat(object $object): bool
    {
        return $object instanceof ReflectionConstant;
    }

    public function format(ObjectFormatter $formatter, object $object): string
    {
        assert($object instanceof ReflectionConstant);

        return sprintf('%s = %s', $object->name(), json_encode($object->value()));
    }
}
