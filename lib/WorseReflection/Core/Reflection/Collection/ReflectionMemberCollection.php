<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

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

    /**
     * @return static
     */
    public function byMemberType(string $type): ReflectionCollection;
}
