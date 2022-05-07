<?php

namespace Phpactor\WorseReflection\Core\Reflector;

use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnum;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\SourceCode;

interface ClassReflector
{
    /**
     * Reflect class.
     * @param Name|string $className
     */
    public function reflectClass($className): ReflectionClass;

    /**
     * Reflect an interface.
     * @param Name|string $className
     */
    public function reflectInterface($className): ReflectionInterface;

    /**
     * Reflect a trait
     * @param Name|string $className
     */
    public function reflectTrait($className): ReflectionTrait;

    /**
     * Reflect an enum
     *
     * @param Name|string $className
     */
    public function reflectEnum($className): ReflectionEnum;

    /**
     * Reflect a class, trait, enum or interface by its name.
     * @param Name|string $className
     */
    public function reflectClassLike($className): ReflectionClassLike;

    /**
     * @param string|Name $className
     */
    public function sourceCodeForClassLike($className): SourceCode;
}
