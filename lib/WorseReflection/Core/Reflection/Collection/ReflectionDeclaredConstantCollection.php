<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\SourceFileNode;
use Phpactor\DocblockParser\Ast\Type\CallableNode;
use Phpactor\Name\QualifiedName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionConstant as CoreReflectionConstant;
use Phpactor\WorseReflection\Core\Reflection\ReflectionDeclaredConstant;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

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

            /**
             * @phpstan-ignore-next-line TP lies
             */
            if (!$callable instanceof QualifiedName) {
                continue;
            }

            /** @phpstan-ignore-next-line */
            if ('define' !== NodeUtil::shortName($callable)) {
                continue;
            }

            $arguments = $descendentNode->argumentExpressionList;
            if (!$arguments) {
                continue;
            }
            $arguments = $arguments->children;
            if (!is_array($arguments)) {
                continue;
            }
            dump($arguments);

            $items[(string) $callable->getNamespacedName()] = new ReflectionDeclaredConstant($sourceCode, $serviceLocator, $descendentNode);
        }

        return new self($items);
    }
}
