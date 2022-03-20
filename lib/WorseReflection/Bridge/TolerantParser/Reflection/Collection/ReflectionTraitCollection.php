<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection;

use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Phpactor\WorseReflection\Core\ClassName;
use Microsoft\PhpParser\Node\TraitUseClause;
use Phpactor\WorseReflection\Bridge\TolerantParser\Patch\TolerantQualifiedNameResolver;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionTraitCollection as CoreReflectionTraitCollection;

/**
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionTrait get()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionTrait first()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionTrait last()
 */
class ReflectionTraitCollection extends AbstractReflectionCollection implements CoreReflectionTraitCollection
{
    public static function fromClassDeclaration(ServiceLocator $serviceLocator, ClassDeclaration $class)
    {
        $items = [];
        /** @var TraitUseClause $memberDeclaration */
        foreach ($class->classMembers->classMemberDeclarations as $memberDeclaration) {
            if (false === $memberDeclaration instanceof TraitUseClause) {
                continue;
            }

            foreach ($memberDeclaration->traitNameList->getValues() as $traitName) {
                $traitName = TolerantQualifiedNameResolver::getResolvedName($traitName);
                try {
                    $items[(string) $traitName] = $serviceLocator->reflector()->reflectTrait(ClassName::fromString($traitName));
                } catch (NotFound $notFound) {
                }
            }
        }

        return new static($serviceLocator, $items);
    }

    protected function collectionType(): string
    {
        return CoreReflectionTraitCollection::class;
    }
}
