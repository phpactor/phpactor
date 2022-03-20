<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection;

use Microsoft\PhpParser\Node\EnumCaseDeclaration;
use Microsoft\PhpParser\Node\Statement\EnumDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionEnum;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionEnumCase as PhpactorReflectionEnumCase;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionEnumCaseCollection as PhpactorReflectionEnumCaseCollection;
use Phpactor\WorseReflection\Core\ServiceLocator;

/**
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionConstant get()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionConstant first()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionConstant last()
 */
final class ReflectionEnumCaseCollection extends ReflectionMemberCollection implements PhpactorReflectionEnumCaseCollection
{
    public static function fromEnumDeclaration(ServiceLocator $serviceLocator, EnumDeclaration $enum, ReflectionEnum $reflectionEnum): self
    {
        $items = [];
        foreach ($enum->enumMembers->enumMemberDeclarations as $member) {
            if (!$member instanceof EnumCaseDeclaration) {
                continue;
            }
            $enumCase = new PhpactorReflectionEnumCase($serviceLocator, $reflectionEnum, $member);
            $items[$enumCase->name()] = $enumCase;
        }

        return new static($serviceLocator, $items);
    }

    protected function collectionType(): string
    {
        return PhpactorReflectionEnumCaseCollection::class;
    }
}
