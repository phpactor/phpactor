<?php

namespace Phpactor\WorseReflection\Core\Inference;

class PropertyAssignments extends Assignments
{
    public static function create()
    {
        return new self([]);
    }

    public static function fromArray(array $assignments): PropertyAssignments
    {
        return new self($assignments);
    }
}
