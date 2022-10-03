<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\FunctionDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction as PhpactorReflectionFunction;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\SourceCode;

/**
 * @extends AbstractReflectionCollection<PhpactorReflectionFunction>
 */
class ReflectionFunctionCollection extends AbstractReflectionCollection
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

        return new self($items);
    }
}
