<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Core\Type;

interface ReflectionMember
{
    public const TYPE_METHOD = 'method';
    public const TYPE_PROPERTY = 'property';
    public const TYPE_CONSTANT = 'constant';
    public const TYPE_ENUM = 'case';

    public function position(): ByteOffsetRange;

    public function declaringClass(): ReflectionClassLike;

    /**
     * Return the original method declaration (in case this method has been
     * overridden).
     *
     * In case the original method is ambiguous (e.g. implemented by two
     * or more interfaces) the first will be returned.
     */
    public function original(): ReflectionMember;

    public function class(): ReflectionClassLike;

    public function name(): string;

    public function nameRange(): ByteOffsetRange;

    public function frame(): Frame;

    public function docblock(): DocBlock;

    public function scope(): ReflectionScope;

    public function visibility(): Visibility;

    public function inferredType(): Type;

    public function type(): Type;

    public function isVirtual(): bool;

    public function isStatic(): bool;

    /**
     * @return self::TYPE_*
     */
    public function memberType(): string;

    public function deprecation(): Deprecation;

    public function withClass(ReflectionClassLike $class): self;
}
