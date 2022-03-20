<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection;

use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node\SourceFileNode;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionFunctionCollection as CoreReflectionFunctionCollection;
use Microsoft\PhpParser\Node\Statement\FunctionDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionFunction;

/**
 * @method \Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionFunction get()
 */
class ReflectionFunctionCollection extends AbstractReflectionCollection implements CoreReflectionFunctionCollection
{
    public static function fromNode(ServiceLocator $serviceLocator, SourceCode $sourceCode, SourceFileNode $node): self
    {
        $items = [];
        foreach ($node->getDescendantNodes() as $descendentNode) {
            if (!$descendentNode instanceof FunctionDeclaration) {
                continue;
            }

            $items[(string) $descendentNode->getNamespacedName()] = new ReflectionFunction($sourceCode, $serviceLocator, $descendentNode);
        }

        return new self($serviceLocator, $items);
    }

    protected function collectionType(): string
    {
        return CoreReflectionFunctionCollection::class;
    }
}
