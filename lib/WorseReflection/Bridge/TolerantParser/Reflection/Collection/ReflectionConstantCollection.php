<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection;

use Microsoft\PhpParser\Node\ConstElement;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionConstant;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionConstantCollection as CoreReflectionConstantCollection;

/**
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionConstant get()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionConstant first()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionConstant last()
 */
class ReflectionConstantCollection extends ReflectionMemberCollection implements CoreReflectionConstantCollection
{
    public static function fromClassDeclaration(ServiceLocator $serviceLocator, ClassDeclaration $class, ReflectionClass $reflectionClass)
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

        return new static($serviceLocator, $items);
    }

    public static function fromReflectionConstants(ServiceLocator $serviceLocator, array $constants)
    {
        return new static($serviceLocator, $constants);
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
        return new static($serviceLocator, $items);
    }

    protected function collectionType(): string
    {
        return CoreReflectionConstantCollection::class;
    }
}
