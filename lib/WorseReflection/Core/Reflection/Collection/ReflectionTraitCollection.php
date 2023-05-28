<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Microsoft\PhpParser\Node\Statement\EnumDeclaration;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Phpactor\WorseReflection\Core\ClassName;
use Microsoft\PhpParser\Node\TraitUseClause;
use Phpactor\WorseReflection\Bridge\TolerantParser\Patch\TolerantQualifiedNameResolver;

/**
 * @extends AbstractReflectionCollection<ReflectionTrait>
 */
class ReflectionTraitCollection extends AbstractReflectionCollection
{
    public static function fromClassDeclaration(ServiceLocator $serviceLocator, ClassDeclaration $class): self
    {
        $items = [];
        foreach ($class->classMembers->classMemberDeclarations as $memberDeclaration) {
            if (false === $memberDeclaration instanceof TraitUseClause) {
                continue;
            }

            if ($memberDeclaration->traitNameList === null) {
                continue;
            }

            foreach ($memberDeclaration->traitNameList->getValues() as $traitName) {
                $traitName = TolerantQualifiedNameResolver::getResolvedName($traitName);
                try {
                    $items[(string) $traitName] = $serviceLocator->reflector()->reflectTrait(ClassName::fromString($traitName));
                } catch (NotFound) {
                }
            }
        }

        return new self($items);
    }

    /**
     * @param array<string,bool> $visited
     */
    public static function fromTraitDeclaration(ServiceLocator $serviceLocator, TraitDeclaration $traitDeclaration, array $visited = []): self
    {
        $items = [];
        foreach ($traitDeclaration->traitMembers->traitMemberDeclarations as $memberDeclaration) {
            if (false === $memberDeclaration instanceof TraitUseClause) {
                continue;
            }

            foreach ($memberDeclaration->traitNameList->getValues() as $traitName) {
                $traitName = TolerantQualifiedNameResolver::getResolvedName($traitName);
                try {
                    $items[(string) $traitName] = $serviceLocator->reflector()->reflectTrait(ClassName::fromString($traitName), $visited);
                } catch (NotFound) {
                }
            }
        }

        return new self($items);
    }

    public static function fromEnumDeclaration(ServiceLocator $serviceLocator, EnumDeclaration $enumDeclaration): ReflectionTraitCollection
    {
        $items = [];
        /** @phpstan-ignore-next-line not trusting TP */
        foreach ($enumDeclaration?->enumMembers?->enumMemberDeclarations ?? [] as $memberDeclaration) {
            if (false === $memberDeclaration instanceof TraitUseClause) {
                continue;
            }

            foreach ($memberDeclaration->traitNameList->getValues() as $traitName) {
                $traitName = TolerantQualifiedNameResolver::getResolvedName($traitName);
                try {
                    $items[(string) $traitName] = $serviceLocator->reflector()->reflectTrait(ClassName::fromString($traitName));
                } catch (NotFound) {
                }
            }
        }

        return new self($items);
    }
}
