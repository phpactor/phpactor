<?php

namespace Phpactor\ReferenceFinder;

use Phpactor\TextDocument\LocationRange;
use Phpactor\WorseReflection\Core\Type;

class TypeLocation
{
    public function __construct(private Type $type, private LocationRange $range)
    {
    }

    public function type(): Type
    {
        return $this->type;
    }

    public function range(): LocationRange
    {
        return $this->range;
    }
}
