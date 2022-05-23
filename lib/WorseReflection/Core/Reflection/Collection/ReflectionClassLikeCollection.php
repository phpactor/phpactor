<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\EnumDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionEnum;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass as PhpactorReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnum as PhpactorReflectionEnum;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface as PhpactorReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait as PhpactorReflectionTrait;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionClass;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\SourceCode;
use Microsoft\PhpParser\ClassLike;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionClassDecorator;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionInterfaceDecorator;

/**
 * @extends AbstractReflectionCollection<PhpactorReflectionClass|PhpactorReflectionEnum|PhpactorReflectionTrait|PhpactorReflectionInterface>
 */
final class ReflectionClassLikeCollection extends AbstractReflectionCollection
{
    public static function fromNode(ServiceLocator $serviceLocator, SourceCode $source, Node $node): self
    {
        $items = [];

        $nodeCollection = $node->getDescendantNodes(function (Node $node) {
            return false === $node instanceof ClassLike;
        });

        foreach ($nodeCollection as $child) {
            if (false === $child instanceof ClassLike) {
                continue;
            }

            if ($child instanceof TraitDeclaration) {
                $items[(string) $child->getNamespacedName()] =  new ReflectionTrait($serviceLocator, $source, $child);
                continue;
            }

            if ($child instanceof EnumDeclaration) {
                $items[(string) $child->getNamespacedName()] =  new ReflectionEnum($serviceLocator, $source, $child);
                continue;
            }

            if ($child instanceof InterfaceDeclaration) {
                $items[(string) $child->getNamespacedName()] =  new VirtualReflectionInterfaceDecorator(
                    $serviceLocator,
                    new ReflectionInterface($serviceLocator, $source, $child),
                    $serviceLocator->methodProviders()
                );
                continue;
            }

            if ($child instanceof ClassDeclaration) {
                $items[(string) $child->getNamespacedName()] = new VirtualReflectionClassDecorator(
                    $serviceLocator,
                    new ReflectionClass($serviceLocator, $source, $child),
                    $serviceLocator->methodProviders()
                );
            }
        }

        return new static($items);
    }

    public function classes(): ReflectionClassCollection
    {
        return new ReflectionClassCollection(iterator_to_array($this->byMemberClass(PhpactorReflectionClass::class)));
    }

    public function concrete(): self
    {
        return new static(array_filter($this->items, function ($item) {
            return $item->isConcrete();
        }));
    }
}
