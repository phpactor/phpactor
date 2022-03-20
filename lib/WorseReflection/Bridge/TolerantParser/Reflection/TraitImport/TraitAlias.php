<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TraitImport;

use Phpactor\WorseReflection\Core\Visibility;

class TraitAlias
{
    private $originalName;

    private $visiblity;

    private $newName;

    public function __construct(string $originalName, Visibility $visiblity = null, string $newName)
    {
        $this->originalName = $originalName;
        $this->visiblity = $visiblity;
        $this->newName = $newName;
    }

    public function originalName(): string
    {
        return $this->originalName;
    }

    public function visiblity(Visibility $default = null): Visibility
    {
        return $this->visiblity ?: $default ?: Visibility::public();
    }

    public function newName(): string
    {
        return $this->newName;
    }
}
