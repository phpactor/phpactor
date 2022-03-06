<?php

namespace Phpactor\ClassMover\Domain\Name;

class Namespace_ extends QualifiedName
{
    public function qualify(QualifiedName $name): FullyQualifiedName
    {
        return FullyQualifiedName::fromString($this->__toString().'\\'.$name->__toString());
    }

    public function isRoot(): bool
    {
        return count($this->parts) === 0;
    }
}
