<?php

namespace Phpactor\WorseReflection\Core\DocBlock;

use Phpactor\WorseReflection\Core\Types;

class DocBlockVar
{
    private string $name;
    
    private Types $types;

    public function __construct(string $name, Types $types)
    {
        $this->name = $name;
        $this->types = $types;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function types(): Types
    {
        return $this->types;
    }
}
