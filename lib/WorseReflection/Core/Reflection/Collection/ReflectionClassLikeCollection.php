<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\EnumDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionEnum;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass as PhpactorReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionClass;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionTrait;
use Phpactor\TextDocument\TextDocument;
use Microsoft\PhpParser\ClassLike;
use Microsoft\PhpParser\Node;

/**
 * @extends AbstractReflectionCollection<ReflectionClassLike>
 */
final class ReflectionClassLikeCollection extends AbstractReflectionCollection
{
    /**
     * @param array<string,bool> $visited
     */
    public static function fromNode(ServiceLocator $serviceLocator, TextDocument $source, Node $node, array $visited = []): self
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
                $items[(string) $child->getNamespacedName()] =  new ReflectionTrait($serviceLocator, $source, $child, $visited);
                continue;
            }

            if ($child instanceof EnumDeclaration) {
                $items[(string) $child->getNamespacedName()] =  new ReflectionEnum($serviceLocator, $source, $child);
                continue;
            }

            if ($child instanceof InterfaceDeclaration) {
                $items[(string) $child->getNamespacedName()] =  new ReflectionInterface($serviceLocator, $source, $child, $visited);
                continue;
            }

            if ($child instanceof ClassDeclaration) {
                $items[(string) $child->getNamespacedName()] = new ReflectionClass($serviceLocator, $source, $child, $visited);
            }
        }

        return new static($items);
    }

    public function classes(): ReflectionClassCollection
    {
        /** @phpstan-ignore-next-line */
        return new ReflectionClassCollection(iterator_to_array($this->byMemberClass(PhpactorReflectionClass::class)));
    }

    public function concrete(): self
    {
        return new static(array_filter($this->items, function ($item) {
            return $item->isConcrete();
        }));
    }
}
