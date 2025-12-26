<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\MissingType;

class MemberTypeResolver
{
    const TYPE_METHODS = 'methods';
    const TYPE_CONSTANTS = 'constants';
    const TYPE_PROPERTIES = 'properties';

    public function __construct(private readonly ClassReflector $reflector)
    {
    }

    public function methodType(Type $containerType, NodeContext $info, string $name): NodeContext
    {
        return $this->memberType(self::TYPE_METHODS, $containerType, $info, $name);
    }

    public function constantType(Type $containerType, NodeContext $info, string $name): NodeContext
    {
        return $this->memberType(self::TYPE_CONSTANTS, $containerType, $info, $name);
    }

    public function propertyType(Type $containerType, NodeContext $info, string $name): NodeContext
    {
        if (mb_substr($name, 0, 1) == '$') {
            $name = mb_substr($name, 1);
        }
        return $this->memberType(self::TYPE_PROPERTIES, $containerType, $info, $name);
    }

    /**
     * @return ReflectionClassLike
     */
    private function reflectClassOrNull(ClassType $containerType, string $name)
    {
        return $this->reflector->reflectClassLike($containerType->name);
    }

    private function memberType(string $memberType, Type $containerType, NodeContext $info, string $name)
    {
        if ($containerType instanceof MissingType) {
            return $info->withIssue(sprintf(
                'No type available for containing class "%s" for method "%s"',
                (string) $containerType,
                $name
            ));
        }

        if (!$containerType instanceof ClassType) {
            return $info->withIssue(sprintf(
                'Containing type is not a class, got "%s"',
                (string) $containerType
            ));
        }

        try {
            $class = $this->reflectClassOrNull($containerType, $name);
        } catch (NotFound $e) {
            $info = $info->withIssue(sprintf(
                'Could not find container class "%s" for "%s"',
                (string) $containerType,
                $name
            ));

            return $info;
        }

        $info = $info->withContainerType(TypeFactory::reflectedClass($this->reflector, $class->name()));

        if (!method_exists($class, $memberType)) {
            $info = $info->withIssue(sprintf(
                'Container class "%s" has no method "%s"',
                (string) $containerType,
                $memberType
            ));

            return $info;
        }

        try {
            if (false === $class->$memberType()->has($name)) {
                $info = $info->withIssue(sprintf(
                    'Class "%s" has no %s named "%s"',
                    (string) $containerType,
                    $memberType,
                    $name
                ));

                return $info;
            }
        } catch (NotFound $e) {
            $info = $info->withIssue($e->getMessage());
            return $info;
        }

        $member = $class->$memberType()->get($name);
        assert($member instanceof ReflectionMember);
        $declaringClass = $member->declaringClass();
        $info = $info->withContainerType(TypeFactory::reflectedClass($this->reflector, $declaringClass->name()));

        return $info->withType($member->inferredType());
    }
}
