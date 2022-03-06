<?php

namespace Phpactor\Completion\Bridge\WorseReflection\Formatter;

use Phpactor\WorseReflection\Core\Types;
use Phpactor\Completion\Core\Formatter\Formatter;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;

class TypesFormatter implements Formatter
{
    public function canFormat(object $object): bool
    {
        return $object instanceof Types;
    }

    public function format(ObjectFormatter $formatter, object $object): string
    {
        assert($object instanceof Types);

        if (Types::empty() == $object) {
            return '<unknown>';
        }

        $formattedTypes = [];
        foreach ($object as $type) {
            $formattedTypes[] = $formatter->format($type);
        }

        return implode('|', $formattedTypes);
    }
}
