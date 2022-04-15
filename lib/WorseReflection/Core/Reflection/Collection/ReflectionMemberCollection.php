<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;

/**
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionMember first()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionMember last()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionMember get(string $name)
 *
 *
 * @template T of ReflectionMember
 * @extends ReflectionCollection<T>
 */
interface ReflectionMemberCollection extends ReflectionCollection
{
    /**
     * By member type: constant, method, or property
     *
     * @param ReflectionMember::TYPE_* $type
     * @return ReflectionMemberCollection<T>
     */
    public function byMemberType(string $type): ReflectionMemberCollection;

    /**
     * @return ReflectionMemberCollection<T>
     */
    public function byVisibilities(array $visibilities): ReflectionMemberCollection;

    /**
     * @return ReflectionMemberCollection<T>
     */
    public function belongingTo(ClassName $class): ReflectionMemberCollection;

    /**
     * @return ReflectionMemberCollection<T>
     */
    public function atOffset(int $offset): ReflectionMemberCollection;

    /**
     * @return ReflectionMemberCollection<T>
     */
    public function byName(string $name): ReflectionMemberCollection;

    /**
     * @return ReflectionMemberCollection<T>
     */
    public function virtual(): ReflectionMemberCollection;

    /**
     * @return ReflectionMemberCollection<T>
     */
    public function real(): ReflectionMemberCollection;

    /**
     * @return ReflectionMethodCollection<T>
     */
    public function methods(): ReflectionMethodCollection;

    /**
     * @return ReflectionPropertyCollection<T>
     */
    public function properties(): ReflectionPropertyCollection;
}
