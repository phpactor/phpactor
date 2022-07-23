<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Closure;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionConstant;
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
        $new = new self([]);
        foreach ($class->classMembers->classMemberDeclarations as $member) {

            if ($member instanceof ClassConstDeclaration) {
                /** @phpstan-ignore-next-line TP: lie */
                if (!$member->constElements) {
                    continue;
                }

                foreach ($member->constElements->getElements() as $constElement) {
                    $new->constants[$constElement->getName()] = new ReflectionConstant($serviceLocator, $reflectionClass, $member, $constElement);
                    $new->items[$constElement->getName()] = $new->constants[$constElement->getName()];
                }
            }
        }

        return $new;
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
            $this->items[$member->name()] = $member;
            if ($member instanceof ReflectionConstant) {
                $this->constants[$member->name()] = $member;
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
