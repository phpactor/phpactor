<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;

interface ReflectionClassLike extends ReflectionNode
{
    public function position(): Position;

    public function name(): ClassName;

    public function methods(ReflectionClassLike $contextClass = null): ReflectionMethodCollection;

    public function members(): ReflectionMemberCollection;

    public function sourceCode(): SourceCode;

    public function isInterface(): bool;

    public function isInstanceOf(ClassName $className): bool;

    public function isTrait(): bool;

    public function isClass(): bool;

    public function isEnum(): bool;

    public function isConcrete();

    public function docblock(): DocBlock;

    public function deprecation(): Deprecation;
}
