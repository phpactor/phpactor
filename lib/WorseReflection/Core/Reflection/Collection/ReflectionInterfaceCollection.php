<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Microsoft\PhpParser\Node\ClassInterfaceClause;
use Microsoft\PhpParser\Node\InterfaceBaseClause;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\WorseReflection\Core\ClassName;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionInterfaceCollection as CoreReflectionInterfaceCollection;

/**
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionInterface get()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionInterface first()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionInterface last()
 */
class ReflectionInterfaceCollection extends AbstractReflectionCollection implements CoreReflectionInterfaceCollection
{
    public static function fromInterfaceDeclaration(ServiceLocator $serviceLocator, InterfaceDeclaration $interface): self
    {
        return self::fromBaseClause($serviceLocator, $interface->interfaceBaseClause);
    }

    public static function fromClassDeclaration(ServiceLocator $serviceLocator, ClassDeclaration $class): self
    {
        return self::fromBaseClause($serviceLocator, $class->classInterfaceClause);
    }

    protected function collectionType(): string
    {
        return CoreReflectionInterfaceCollection::class;
    }

    /**
     * @param mixed $baseClause
     */
    private static function fromBaseClause(ServiceLocator $serviceLocator, $baseClause): self
    {
        if (!$baseClause instanceof ClassInterfaceClause && !$baseClause instanceof InterfaceBaseClause) {
            return new self([]);
        }

        $items = [];
        $interfaceNameList = $baseClause->interfaceNameList;

        if (null === $interfaceNameList) {
            return new self([]);
        }

        $children = $interfaceNameList->children;

        if (!$children) {
            return new self([]);
        }

        foreach ($children as $name) {
            if (false === $name instanceof QualifiedName) {
                continue;
            }

            try {
                $interface = $serviceLocator->reflector()->reflectInterface(
                    ClassName::fromString((string) $name->getResolvedName())
                );
                $items[$interface->name()->full()] = $interface;
            } catch (NotFound $e) {
            }
        }

        return new self($items);
    }
}
