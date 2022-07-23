<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Closure;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionConstant;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Traversable;


/**
 * @extends AbstractReflectionCollection<ReflectionMember>
 */
final class ClassLikeReflectionMemberCollection extends AbstractReflectionCollection implements ReflectionMemberCollection
{
    /**
     * @var ReflectionConstant[]
     */
    private array $constants = [];

    public static function fromClassMemberDeclarations(
        ServiceLocator $serviceLocator,
        ClassDeclaration $class,
        ReflectionClass $reflectionClass
    ): ReflectionMemberCollection
    {
        return self::fromDeclarations(
            $serviceLocator,
            $reflectionClass,
            $class->classMembers->classMemberDeclarations,
        );
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
            }
        }

        return $new;
    }


    public static function fromInterfaceMemberDeclarations(ServiceLocator $serviceLocator, InterfaceDeclaration $interfaceDeclaration, ReflectionInterface $reflectionInterface): self
    {
        return self::fromDeclarations(
            $serviceLocator,
            $class,
            $interfaceDeclaration->interfaceMembers->interfaceMemberDeclarations,
        );
    }

    public function getIterator(): Traversable
    {
        foreach ([
            $this->constants
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
            return $member->memberType();
        });
    }

    public function byVisibilities(array $visibilities): ReflectionMemberCollection
    {
        return $this->filter(function (ReflectionMember $member) use ($visibilities) {
            foreach ($visibilities as $visiblity) {
                if ($visiblity->__toString() === $member->visibility()->__toString()) {
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
            return $member->position()->start() == $offset;
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
        return new ReflectionMethodCollection();
    }

    public function properties(): ReflectionPropertyCollection
    {
        return new ReflectionPropertyCollection();
    }

    public function constants(): ReflectionConstantCollection
    {
        return new ReflectionConstantCollection($this->constants);
    }

    private function filter(Closure $closure): ReflectionCollection
    {
        $new = new self([]);
        foreach ([
            'constants',
        ] as $collection) {
        $new->$collection = array_map($closure, $this->$collection);
        }

        return $new;
    }
}
