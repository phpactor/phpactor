<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\MemberTypeResolver;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class MemberAccessExpressionResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof MemberAccessExpression);

        $class = $resolver->resolveNode($frame, $node->dereferencableExpression);

        return self::infoFromMemberAccess($resolver, $frame, $class->type(), $node);
    }

    public static function infoFromMemberAccess(NodeContextResolver $resolver, Frame $frame, Type $classType, Node $node): NodeContext
    {
        assert($node instanceof MemberAccessExpression || $node instanceof ScopedPropertyAccessExpression);

        $memberName = NodeUtil::nameFromTokenOrNode($node, $node->memberName);
        $memberType = $node->getParent() instanceof CallExpression ? Symbol::METHOD : Symbol::PROPERTY;

        if ($node->memberName instanceof Node) {
            $memberNameInfo = $resolver->resolveNode($frame, $node->memberName);
            if (is_string($memberNameInfo->value())) {
                $memberName = $memberNameInfo->value();
            }
        }

        if (
            Symbol::PROPERTY === $memberType
            && $node instanceof ScopedPropertyAccessExpression
            && is_string($memberName)
            && substr($memberName, 0, 1) !== '$'
        ) {
            $memberType = Symbol::CONSTANT;
        }

        $information = NodeContextFactory::create(
            (string)$memberName,
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'symbol_type' => $memberType,
            ]
        );

        // if the classType is a call expression, then this is a method call
        $info = (
            new MemberTypeResolver($resolver->reflector())
        )->{$memberType . 'Type'}($classType, $information, $memberName);

        if (Symbol::PROPERTY === $memberType) {
            $frameTypes = self::getFrameTypesForPropertyAtPosition(
                $frame,
                (string) $memberName,
                $classType,
                $node->getEndPosition(),
            );

            foreach ($frameTypes as $types) {
                $info = $info->withTypes(
                    $info->types()->merge($types),
                );
            }
        }

        return $info;
    }

    private static function getFrameTypesForPropertyAtPosition(
        Frame $frame,
        string $propertyName,
        Type $classType,
        int $position
    ): Generator {
        $assignments = $frame->properties()
            ->lessThanOrEqualTo($position)
            ->byName($propertyName)
        ;

        if (!$classType instanceof ClassType) {
            return;
        }

        foreach ($assignments as $variable) {
            $containerType = $variable->classType();

            if (!$containerType) {
                continue;
            }

            if (!$containerType instanceof ClassType) {
                continue;
            }

            if ($containerType->name != $classType->name) {
                continue;
            }

            yield $variable->types();
        }
    }
}
