<?php

namespace Phpactor\ReferenceFinder;

use Phpactor\TextDocument\Location;
use Phpactor\WorseReflection\Core\Type;

class TypeLocation
{
    public function __construct(
        private readonly Type $type,
        private readonly Location $location
    ) {
    }

    public function type(): Type
    {
        return $this->type;
    }

    public function location(): Location
    {
        return $this->location;
    }
}
