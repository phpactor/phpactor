<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\SourceFileNode;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionDeclaredConstant as PhpactorReflectionDeclaredConstant;
use Phpactor\WorseReflection\Core\Reflection\ReflectionDeclaredConstant;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

/**
 * @extends AbstractReflectionCollection<ReflectionDeclaredConstant>
 */
class ReflectionDeclaredConstantCollection extends AbstractReflectionCollection
{
    /**
     * @param ReflectionDeclaredConstant[] $constants
     */
    public static function fromReflectionConstants(array $constants): self
    {
        return new self($constants);
    }

    public static function fromNode(ServiceLocator $serviceLocator, TextDocument $sourceCode, SourceFileNode $node): ReflectionDeclaredConstantCollection
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

            if ('define' !== NodeUtil::shortName($callable)) {
                continue;
            }

            $constant = new PhpactorReflectionDeclaredConstant(
                $serviceLocator,
                $sourceCode,
                $descendentNode
            );
            $items[$constant->name()->__toString()] = $constant;
        }

        return new self($items);
    }
}
