<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\NameImports;
use Phpactor\WorseReflection\Core\Type;

interface ReflectionScope
{
    public function nameImports(): NameImports;

    public function resolveLocalName(Name $type): Name;

    public function resolveFullyQualifiedName($type, ReflectionClassLike $classLike = null): Type;
}
