<?php

namespace Phpactor\WorseReflection\Core\DocBlock;

use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\WorseReflection\Core\Inference\TypeAssertion;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\TemplateMap;
use Phpactor\WorseReflection\Core\Type;

interface DocBlock
{
    public function typeAliases(): DocBlockTypeAliases;

    public function methods(ReflectionClassLike $declaringClass): ReflectionMethodCollection;

    public function properties(ReflectionClassLike $declaringClass): ReflectionPropertyCollection;

    public function isDefined(): bool;

    public function raw(): string;

    public function formatted(): string;

    public function returnType(): Type;

    public function methodType(string $methodName): Type;

    public function propertyType(string $methodName): Type;

    public function parameterType(string $paramName): Type;

    public function vars(): DocBlockVars;

    public function params(): DocBlockParams;

    public function inherits(): bool;

    public function deprecation(): Deprecation;

    public function templateMap(): TemplateMap;

    /**
     * @return Type[]
     */
    public function extends(): array;

    /**
     * @return Type[]
     */
    public function implements(): array;

    /**
     * @return Type[]
     */
    public function mixins(): array;

    /**
     * @return DocBlockTypeAssertion[]
     */
    public function assertions(): array;
}
