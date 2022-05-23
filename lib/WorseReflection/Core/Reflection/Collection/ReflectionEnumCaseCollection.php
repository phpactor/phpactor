<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Microsoft\PhpParser\Node\EnumCaseDeclaration;
use Microsoft\PhpParser\Node\Statement\EnumDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionEnum;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionEnumCase as PhpactorReflectionEnumCase;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnumCase;
use Phpactor\WorseReflection\Core\ServiceLocator;

/**
 * @extends HomogeneousReflectionMemberCollection<ReflectionEnumCase>
 */
final class ReflectionEnumCaseCollection extends HomogeneousReflectionMemberCollection
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

        return new static($items);
    }
}
