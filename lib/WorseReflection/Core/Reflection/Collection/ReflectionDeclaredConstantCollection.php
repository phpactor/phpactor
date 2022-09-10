<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\StringLiteral;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionDeclaredConstant as PhpactorReflectionDeclaredConstant;
use Phpactor\WorseReflection\Core\Reflection\ReflectionDeclaredConstant;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\SourceCode;
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

    public static function fromNode(ServiceLocator $serviceLocator, SourceCode $sourceCode, SourceFileNode $node): ReflectionDeclaredConstantCollection
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
            $arguments = iterator_to_array($arguments->getElements());
            if (!is_array($arguments)) {
                continue;
            }
            if (count($arguments) < 2) {
                continue;
            }

            $name = $arguments[0];
            $value = $arguments[1];

            if (!$name instanceof ArgumentExpression && !$value instanceof ArgumentExpression) {
                continue;
            }

            $name = $name->expression;
            if (!$name instanceof StringLiteral) {
                continue;
            }

            $items[(string) $name->getStringContentsText()] = new PhpactorReflectionDeclaredConstant($serviceLocator, $name, $value);
        }

        return new self($items);
    }
}
