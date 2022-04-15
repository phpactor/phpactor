<?php

namespace Phpactor\ReferenceFinder;

use Phpactor\TextDocument\Location;
use Phpactor\WorseReflection\Core\Type;

class TypeLocation
{
    private Type $type;

    private Location $location;

    public function __construct(Type $type, Location $location)
    {
        $this->type = $type;
        $this->location = $location;
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
