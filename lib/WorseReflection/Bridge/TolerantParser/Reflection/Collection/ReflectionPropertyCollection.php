<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection;

use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Parameter;
use Microsoft\PhpParser\Node\Statement\EnumDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionPromotedProperty;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnum;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionProperty;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionTrait;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection as CoreReflectionPropertyCollection;

/**
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionProperty get(string $name)
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionProperty first()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionProperty last()
 */
final class ReflectionPropertyCollection extends ReflectionMemberCollection implements CoreReflectionPropertyCollection
{
    /**
     * @return static
     */
    public static function fromClassDeclarationConstructorPropertyPromotion(
        ServiceLocator $serviceLocator,
        ClassDeclaration $class,
        ReflectionClassLike $reflectionClass
    ): self {
        if (!$reflectionClass instanceof ReflectionClass) {
            return new static($serviceLocator, []);
        }
        $properties = [];
        foreach ($class->classMembers->classMemberDeclarations as $classMember) {
            if (!$classMember instanceof MethodDeclaration) {
                continue;
            }

            if ($classMember->getName() !== '__construct') {
                continue;
            }

            $parameters = $classMember->parameters;
            /** @phpstan-ignore-next-line */
            if (!$parameters) {
                continue;
            }
            $children = $parameters->children;
            if (!$children) {
                continue;
            }
            $properties = array_merge($properties, array_filter($children, function ($member) {
                if (!$member instanceof Parameter) {
                    return false;
                }
                return $member->visibilityToken !== null;
            }));
        }

        $items = [];
        foreach ($properties as $property) {
            if (!$property instanceof Parameter) {
                continue;
            }
            $items[$property->getName()] = new ReflectionPromotedProperty($serviceLocator, $reflectionClass, $property);
        }

        return new static($serviceLocator, $items);
    }

    /**
     * @return static
     */
    public static function fromClassDeclaration(ServiceLocator $serviceLocator, ClassDeclaration $class, ReflectionClassLike $reflectionClass): self
    {
        /** @var PropertyDeclaration[] $properties */
        $properties = array_filter($class->classMembers->classMemberDeclarations, function ($member) {
            return $member instanceof PropertyDeclaration;
        });

        $items = [];
        foreach ($properties as $property) {
            foreach ($property->propertyElements as $propertyElement) {
                foreach ($propertyElement as $variable) {
                    if ($variable instanceof AssignmentExpression) {
                        $variable = $variable->leftOperand;
                    }

                    if (false === $variable instanceof Variable) {
                        continue;
                    }
                    $items[$variable->getName()] = new ReflectionProperty($serviceLocator, $reflectionClass, $property, $variable);
                }
            }
        }

        return new static($serviceLocator, $items);
    }

    public static function fromTraitDeclaration(ServiceLocator $serviceLocator, TraitDeclaration $trait, ReflectionTrait $reflectionTrait)
    {
        /** @var PropertyDeclaration[] $properties */
        $properties = array_filter($trait->traitMembers->traitMemberDeclarations, function ($member) {
            return $member instanceof PropertyDeclaration;
        });

        $items = [];
        foreach ($properties as $property) {
            foreach ($property->propertyElements as $propertyElement) {
                foreach ($propertyElement as $variable) {
                    if (false === $variable instanceof Variable) {
                        continue;
                    }
                    $items[$variable->getName()] = new ReflectionProperty($serviceLocator, $reflectionTrait, $property, $variable);
                }
            }
        }

        return new static($serviceLocator, $items);
    }

    public static function fromEnumDeclaration(ServiceLocator $serviceLocator, EnumDeclaration $enum, ReflectionEnum $reflectionEnum)
    {
        /** @var PropertyDeclaration[] $properties */
        $properties = array_filter($enum->enumMembers->enumMemberDeclarations, function ($member) {
            return $member instanceof PropertyDeclaration;
        });

        $items = [];
        foreach ($properties as $property) {
            foreach ($property->propertyElements as $propertyElement) {
                foreach ($propertyElement as $variable) {
                    if (false === $variable instanceof Variable) {
                        continue;
                    }
                    $items[$variable->getName()] = new ReflectionProperty($serviceLocator, $reflectionEnum, $property, $variable);
                }
            }
        }

        return new static($serviceLocator, $items);
    }

    protected function collectionType(): string
    {
        return CoreReflectionPropertyCollection::class;
    }
}
