<?php

namespace Phpactor\WorseReflection\Core\DocBlock;

use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Types;

interface DocBlock
{
    public function methods(ReflectionClassLike $declaringClass): ReflectionMethodCollection;

    public function properties(ReflectionClassLike $declaringClass): ReflectionPropertyCollection;

    public function isDefined(): bool;

    public function raw(): string;

    public function formatted(): string;

    public function returnTypes(): Types;

    public function methodTypes(string $methodName): Types;

    public function propertyTypes(string $methodName): Types;

    public function parameterTypes(string $paramName): Types;

    public function vars(): DocBlockVars;

    public function inherits(): bool;

    public function deprecation(): Deprecation;
}
