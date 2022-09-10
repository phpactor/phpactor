<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\SourceFileNode;
use Phpactor\DocblockParser\Ast\Type\CallableNode;
use Phpactor\Name\QualifiedName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionConstant as CoreReflectionConstant;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\SourceCode;

/**
 * @extends HomogeneousReflectionMemberCollection<CoreReflectionConstant>
 */
class ReflectionDeclaredConstantCollection extends HomogeneousReflectionMemberCollection
{
    /**
     * @param CoreReflectionConstant[] $constants
     */
    public static function fromReflectionConstants(array $constants): self
    {
        return new self($constants);
    }

    public static function fromNode(ServiceLocator $serviceLocator, SourceCode $sourceCode, SourceFileNode $node)
    {
        $items = [];
        foreach ($node->getDescendantNodes() as $descendentNode) {
            if (!$descendentNode instanceof CallExpression) {
                continue;
            }

            $callable = $descendentNode->callableExpression;

            if (!$callable instanceof QualifiedName) {
                continue;
            }

            $items[(string) $descendentNode->getNamespacedName()] = new ReflectionFunction($sourceCode, $serviceLocator, $descendentNode);
        }

        return new self($items);
    }
}
