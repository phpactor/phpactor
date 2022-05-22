<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Microsoft\PhpParser\Node\ConstElement;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionConstant;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\OldCollection\ReflectionConstantCollection as CoreReflectionConstantCollection;

/**
 * @method ReflectionConstant get()
 * @method ReflectionConstant first()
 * @method ReflectionConstant last()
 * @method ReflectionConstantCollection merge(ReflectionConstantCollection $collection)
 * @method ReflectionMethodCollection empty()
 */
class ReflectionConstantCollection extends ReflectionMemberCollection implements CoreReflectionConstantCollection
{
    public static function fromClassDeclaration(ServiceLocator $serviceLocator, ClassDeclaration $class, ReflectionClass $reflectionClass): CoreReflectionConstantCollection
    {
        $items = [];
        foreach ($class->classMembers->classMemberDeclarations as $member) {
            if (!$member instanceof ClassConstDeclaration) {
                continue;
            }

            /** @phpstan-ignore-next-line TP: lie */
            if (!$member->constElements) {
                continue;
            }

            foreach ($member->constElements->getElements() as $constElement) {
                $items[$constElement->getName()] = new ReflectionConstant($serviceLocator, $reflectionClass, $member, $constElement);
            }
        }

        return new static($items);
    }

    public static function fromReflectionConstants(ServiceLocator $serviceLocator, array $constants)
    {
        return new static($constants);
    }

    public static function fromInterfaceDeclaration(ServiceLocator $serviceLocator, InterfaceDeclaration $interface, ReflectionInterface $reflectionInterface)
    {
        $items = [];
        foreach ($interface->interfaceMembers->interfaceMemberDeclarations as $member) {
            if (!$member instanceof ClassConstDeclaration) {
                continue;
            }

            foreach ($member->constElements->children as $constElement) {
                assert($constElement instanceof ConstElement);
                $items[$constElement->getName()] = new ReflectionConstant($serviceLocator, $reflectionInterface, $member, $constElement);
            }
        }
        return new static($items);
    }

    protected function collectionType(): string
    {
        return CoreReflectionConstantCollection::class;
    }
}
