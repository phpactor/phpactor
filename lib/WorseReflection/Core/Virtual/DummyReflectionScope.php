<?php

namespace Phpactor\WorseReflection\Core\Virtual;

use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\NameImports;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\Type;

class DummyReflectionScope implements ReflectionScope
{
    public function nameImports(): NameImports
    {
        return new NameImports([]);
    }

    public function resolveLocalName(Name $type): Name
    {
        return $type;
    }

    public function resolveFullyQualifiedName($type, ReflectionClassLike $classLike = null): Type
    {
        return $type;
    }

    public function resolveLocalType(Type $type): Type
    {
        return $type;
    }
}
