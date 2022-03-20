<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TraitImport;

class TraitImport
{
    private $traitName;
    
    private array $traitAliases = [];

    public function __construct(string $traitName, array $traitAliases = [])
    {
        $this->traitName = $traitName;
        $this->traitAliases = $traitAliases;
    }

    public function name(): string
    {
        return $this->traitName;
    }

    public function traitAliases(): array
    {
        return $this->traitAliases;
    }

    public function getAlias($name): TraitAlias
    {
        return $this->traitAliases[$name];
    }

    public function hasAliasFor($name): bool
    {
        return array_key_exists($name, $this->traitAliases);
    }
}
