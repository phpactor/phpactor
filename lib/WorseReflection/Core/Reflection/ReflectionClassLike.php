<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\TemplateMap;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;

interface ReflectionClassLike extends ReflectionNode
{
    /**
     * @param array<int,mixed> $types
     */
    public function withGenericMap(array $types): void;

    public function position(): ByteOffsetRange;

    public function name(): ClassName;

    public function methods(ReflectionClassLike $contextClass = null): ReflectionMethodCollection;

    /**
     * @return ReflectionMemberCollection<ReflectionMember>
     */
    public function members(): ReflectionMemberCollection;

    /**
     * @return ReflectionMemberCollection<ReflectionMember>
     */
    public function ownMembers(): ReflectionMemberCollection;

    public function sourceCode(): TextDocument;

    public function isInstanceOf(ClassName $className): bool;

    public function isConcrete(): bool;

    public function docblock(): DocBlock;

    public function deprecation(): Deprecation;

    public function templateMap(): TemplateMap;

    public function type(): ReflectedClassType;

    public function classLikeType(): string;
}
