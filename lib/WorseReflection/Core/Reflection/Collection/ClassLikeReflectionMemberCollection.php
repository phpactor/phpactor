<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Closure;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Parameter;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionConstant;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionPromotedProperty;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionConstant as PhpactorReflectionConstant;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod as PhpactorReflectionMethod;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty as PhpactorReflectionProperty;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Traversable;

/**
 * @extends AbstractReflectionCollection<ReflectionMember>
 */
final class ClassLikeReflectionMemberCollection extends AbstractReflectionCollection implements ReflectionMemberCollection
{
    private const MEMBER_TYPES = [
        'constants',
        'properties',
        'methods',
    ];

    /**
     * @var PhpactorReflectionConstant[]
     */
    private array $constants = [];

    /**
     * @var PhpactorReflectionProperty[]
     */
    private array $properties = [];

    /**
     * @var PhpactorReflectionMethod[]
     */
    private array $methods = [];

    public static function fromClassMemberDeclarations(
        ServiceLocator $serviceLocator,
        ClassDeclaration $class,
        ReflectionClass $reflectionClass
    ): ReflectionMemberCollection {
        return self::fromDeclarations(
            $serviceLocator,
            $reflectionClass,
            $class->classMembers->classMemberDeclarations,
        );
    }

    public static function fromTraitMemberDeclarations(ServiceLocator $serviceLocator, TraitDeclaration $traitDeclaration, ReflectionTrait $reflectionTrait): self
    {
        return self::fromDeclarations(
            $serviceLocator,
            $reflectionTrait,
            $traitDeclaration->traitMembers->traitMemberDeclarations,
        );
    }

    public static function fromInterfaceMemberDeclarations(ServiceLocator $serviceLocator, InterfaceDeclaration $interfaceDeclaration, ReflectionInterface $reflectionInterface): self
    {
        return self::fromDeclarations(
            $serviceLocator,
            $reflectionInterface,
            $interfaceDeclaration->interfaceMembers->interfaceMemberDeclarations,
        );
    }


    public function getIterator(): Traversable
    {
        foreach ([
            $this->constants,
            $this->properties,
            $this->methods,
        ] as $collection) {
            yield from $collection;
        }
    }

    public function merge(ReflectionCollection $collection): AbstractReflectionCollection
    {
        $new = clone $this;
        foreach ($collection as $member) {
            $new->items[$member->name()] = $member;
            if ($member instanceof ReflectionConstant) {
                $new->constants[$member->name()] = $member;
                continue;
            }
            if ($member instanceof PhpactorReflectionProperty) {
                $new->properties[$member->name()] = $member;
                continue;
            }
            if ($member instanceof PhpactorReflectionMethod) {
                $new->methods[$member->name()] = $member;
                continue;
            }
        }

        return $new;
    }

    public function byMemberClass(string $fqn): ReflectionCollection
    {
        return $this->filter(function (ReflectionMember $member) use ($fqn) {
            return $member instanceof $fqn;
        });
    }

    public function byMemberType(string $type): ReflectionCollection
    {
        return $this->filter(function (ReflectionMember $member) use ($type) {
            return $member->memberType() === $type;
        });
    }

    public function byVisibilities(array $visibilities): ReflectionMemberCollection
    {
        return $this->filter(function (ReflectionMember $member) use ($visibilities) {
            foreach ($visibilities as $visiblity) {
                if ($visiblity == $member->visibility()) {
                    return true;
                }
            }

            return false;
        });
    }

    public function belongingTo(ClassName $class): ReflectionMemberCollection
    {
        return $this->filter(function (ReflectionMember $member) use ($class) {
            return $member->declaringClass()->name() == $class;
        });
    }

    public function atOffset(int $offset): ReflectionMemberCollection
    {
        return $this->filter(function (ReflectionMember $member) use ($offset) {
            return $member->position()->start() <= $offset && $member->position()->end() >= $offset;
        });
    }

    public function byName(string $name): ReflectionMemberCollection
    {
        return $this->filter(function (ReflectionMember $member) use ($name) {
            return $member->name() === $name;
        });
    }

    public function virtual(): ReflectionMemberCollection
    {
        return $this->filter(function (ReflectionMember $member) {
            return $member->isVirtual();
        });
    }

    public function real(): ReflectionMemberCollection
    {
        return $this->filter(function (ReflectionMember $member) {
            return false === $member->isVirtual();
        });
    }

    public function methods(): ReflectionMethodCollection
    {
        return new ReflectionMethodCollection($this->methods);
    }

    public function properties(): ReflectionPropertyCollection
    {
        return new ReflectionPropertyCollection($this->properties);
    }

    public function constants(): ReflectionConstantCollection
    {
        return new ReflectionConstantCollection($this->constants);
    }

    /**
     * @return static
     */
    public function map(Closure $closure): ReflectionCollection
    {
        $new = new self([]);
        foreach (self::MEMBER_TYPES as $collection) {
            $new->$collection = array_map($closure, $this->$collection);
        }
        $new->items = array_merge(...array_map(fn (string $type) => $this->$type, self::MEMBER_TYPES));

        return $new;
    }

    private static function fromDeclarations(ServiceLocator $serviceLocator, ReflectionClassLike $classLike, array $nodes): self
    {
        $new = new self([]);
        foreach ($nodes as $member) {
            if ($member instanceof ClassConstDeclaration) {
                /** @phpstan-ignore-next-line TP: lie */
                if (!$member->constElements) {
                    continue;
                }

                foreach ($member->constElements->getElements() as $constElement) {
                    $new->constants[$constElement->getName()] = new ReflectionConstant($serviceLocator, $classLike, $member, $constElement);
                    $new->items[$constElement->getName()] = $new->constants[$constElement->getName()];
                }
                continue;
            }

            if ($member instanceof PropertyDeclaration) {
                foreach ($member->propertyElements as $propertyElement) {
                    foreach ($propertyElement as $variable) {
                        if ($variable instanceof AssignmentExpression) {
                            $variable = $variable->leftOperand;
                        }

                        if (false === $variable instanceof Variable) {
                            continue;
                        }
                        $new->properties[$variable->getName()] = new ReflectionProperty($serviceLocator, $classLike, $member, $variable);
                        $new->items[$variable->getName()] = $new->properties[$variable->getName()];
                    }
                }
                continue;
            }
            if ($member instanceof MethodDeclaration) {
                $new->items[$member->getName()] = new ReflectionMethod($serviceLocator, $classLike, $member);
                $new->methods[$member->getName()] = $new->items[$member->getName()];

                // promoted properties
                if ($member->getName() === '__construct') {
                    $parameters = $member->parameters;
                    /** @phpstan-ignore-next-line */
                    if (!$parameters) {
                        continue;
                    }
                    $children = $parameters->children;
                    if (!$children) {
                        continue;
                    }
                    foreach (array_filter($children, function ($member) {
                        if (!$member instanceof Parameter) {
                            return false;
                        }
                        return $member->visibilityToken !== null;
                    }) as $promotedParameter) {
                        $new->items[$promotedParameter->getName()] = new ReflectionPromotedProperty($serviceLocator, $classLike, $promotedParameter);
                        $new->properties[$promotedParameter->getName()] = $new->items[$promotedParameter->getName()];
                    }
                }
                continue;
            }
        }


        return $new;
    }

    private function filter(Closure $closure): ReflectionCollection
    {
        $new = new self([]);
        foreach (self::MEMBER_TYPES as $collection) {
            $new->$collection = array_filter($this->$collection, $closure);
        }

        $new->items = array_merge(...array_map(fn (string $type) => $new->$type, self::MEMBER_TYPES));

        return $new;
    }
}
