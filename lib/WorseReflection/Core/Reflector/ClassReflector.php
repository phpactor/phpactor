<?php

namespace Phpactor\WorseReflection\Core\Reflector;

use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnum;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Types;

interface ClassReflector
{
    /**
     * Reflect class.
     * @param Name|string $className
     * @param Type[] $arguments
     */
    public function reflectClass($className, array $arguments = []): ReflectionClass;

    /**
     * Reflect an interface.
     * @param Name|string $className
     * @param Type[] $arguments
     */
    public function reflectInterface($className, array $arguments = []): ReflectionInterface;

    /**
     * Reflect a trait
     * @param Name|string $className
     * @param Type[] $arguments
     */
    public function reflectTrait($className, array $arguments = []): ReflectionTrait;

    /**
     * Reflect a trait
     *
     * @param Name|string $className
     * @param Type[] $arguments
     */
    public function reflectEnum($className, array $arguments = []): ReflectionEnum;

    /**
     * Reflect a class, trait, enum or interface by its name.
     * @param Name|string $className
     * @param Type[] $arguments
     */
    public function reflectClassLike($className, array $arguments = []): ReflectionClassLike;

    /**
     * @param string|Name $className
     */
    public function sourceCodeForClassLike($className): SourceCode;
}
