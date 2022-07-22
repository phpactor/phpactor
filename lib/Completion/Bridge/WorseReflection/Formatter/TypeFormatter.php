<?php

namespace Phpactor\Completion\Bridge\WorseReflection\Formatter;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\Completion\Core\Formatter\Formatter;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\WorseReflection\TypeUtil;

class TypeFormatter implements Formatter
{
    public function canFormat(object $object): bool
    {
        return $object instanceof Type;
    }

    public function format(ObjectFormatter $formatter, object $type): string
    {
        assert($type instanceof Type);
        return TypeUtil::shortenClassTypes($type);
    }
}
