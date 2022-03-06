<?php

namespace Phpactor\ClassMover\Domain\Name;

class FullyQualifiedName extends QualifiedName
{
    public static function fromString(string $string)
    {
        return parent::fromString(trim($string, '\\'));
    }
}
