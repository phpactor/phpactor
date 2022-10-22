<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Closure;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Visibility;

/**
 * @template T of ReflectionMember
 * @extends ReflectionCollection<T>
 */
interface ReflectionMemberCollection extends ReflectionCollection
{
    /**
     * @return static
     * @param Visibility[] $visibilities
     */
    public function byVisibilities(array $visibilities): ReflectionMemberCollection;

    /**
     * @return static
     */
    public function belongingTo(ClassName $class): ReflectionMemberCollection;

    /**
     * @return static
     */
    public function atOffset(int $offset): ReflectionMemberCollection;

    /**
     * @return static
     */
    public function byName(string $name): ReflectionMemberCollection;

    /**
     * @return static
     */
    public function virtual(): ReflectionMemberCollection;

    /**
     * @return static
     */
    public function real(): ReflectionMemberCollection;

    public function methods(): ReflectionMethodCollection;

    public function properties(): ReflectionPropertyCollection;

    public function constants(): ReflectionConstantCollection;

    public function enumCases(): ReflectionEnumCaseCollection;

    /**
     * @return static
     */
    public function byMemberType(string $type): ReflectionCollection;


    /**
     * @param Closure(T): ReflectionMember $mapper
     * @return static
     */
    public function map(Closure $mapper);
}
