<?php

namespace Phpactor\Completion\Bridge\WorseReflection\Formatter;

use Phpactor\WorseReflection\Core\Inference\Variable;
use Phpactor\Completion\Core\Formatter\Formatter;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;

class VariableFormatter implements Formatter
{
    public function canFormat(object $object): bool
    {
        return $object instanceof Variable;
    }

    public function format(ObjectFormatter $formatter, object $object): string
    {
        assert($object instanceof Variable);

        return $formatter->format($object->type());
    }
}
