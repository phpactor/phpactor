<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Types;

interface ReflectionMember
{
    public const TYPE_METHOD = 'method';

    public const TYPE_PROPERTY = 'property';

    public const TYPE_CONSTANT = 'constant';

    public const TYPE_ENUM = 'enum';

    public function position(): Position;

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

    public function frame(): Frame;

    public function docblock(): DocBlock;

    public function scope(): ReflectionScope;

    public function visibility(): Visibility;

    /**
     * Inferred types.
     *
     * Note that this will also return PHP 8.0 union types until the type
     * system has been refactored to support more complex types.
     */
    public function inferredTypes(): Types;

    public function type(): Type;

    public function isVirtual(): bool;

    public function memberType();

    public function deprecation(): Deprecation;
}
