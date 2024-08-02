<?php

namespace Phpactor\Extension\Laravel\Completor;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\StringLiteral;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Extension\Laravel\Adapter\Laravel\LaravelContainerInspector;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\UnionType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\Reflector;

class LaravelConfigCompletor implements TolerantCompletor
{
    public const CONTAINER_CLASSES = [
        'Illuminate\\Support\\Facades\\Config',
        'Illuminate\\Config\\Repository',
    ];
    public const CONTAINER_FUNC = ['config'];
    public const CONTAINER_METHODS = ['has', 'get', 'set', 'getMany', 'prepend', 'push'];

    public function __construct(private Reflector $reflector, private LaravelContainerInspector $inspector)
    {
    }

    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        $inQuote = false;
        if ($node instanceof StringLiteral && $node->parent->parent) {
            $inQuote = true;
            $node = $node->getFirstAncestor(CallExpression::class);
        }
        if ($node instanceof QualifiedName) {
            $node = $node->getFirstAncestor(CallExpression::class);
        }

        if (!$node instanceof CallExpression) {
            return;
        }

        $memberAccess = $node->callableExpression;

        $isFunction = false;
        if ($memberAccess instanceof QualifiedName) {
            $isFunction = in_array(needle: $memberAccess->__toString(), haystack: self::CONTAINER_FUNC);
        }

        if (
            !$memberAccess instanceof ScopedPropertyAccessExpression &&
            !$memberAccess instanceof MemberAccessExpression &&
            !$isFunction
        ) {
            return;
        }

        if (!$isFunction) {
            $methodName = NodeUtil::nameFromTokenOrNode($node, $memberAccess->memberName);

            if (!in_array($methodName, self::CONTAINER_METHODS)) {
                return;
            }

            $position = $memberAccess instanceof MemberAccessExpression ?
                $memberAccess->dereferencableExpression->getEndPosition() :
                $memberAccess->scopeResolutionQualifier->getEndPosition();
        } else {
            /** @var QualifiedName $memberAccess */
            $position = $memberAccess->getEndPosition();
        }

        if ($reflectorOutcome = $this->reflector->reflectOffset($source, $position)) {
            $containerType = $reflectorOutcome->nodeContext()->type();
        }

        $isConfigClass = false;

        foreach (self::CONTAINER_CLASSES as $containerClass) {
            if ($containerType instanceof UnionType) {
                foreach ($containerType->allTypes() as $type) {
                    if ($containerClass === $type->__toString()) {
                        $isConfigClass = true;
                        break;
                    }
                }
                // Exit asap.
                if ($isConfigClass) {
                    break;
                }
            } elseif (!$containerType->instanceof(TypeFactory::class($containerClass))->isFalse()) {
                $isConfigClass = true;
                break;
            }
        }

        if (!$isConfigClass) {
            return;
        }

        foreach ($this->inspector->config() as $key => $value) {
            yield Suggestion::createWithOptions($key, [
                'label' => $key,
                'short_description' => $key,
                'documentation' => sprintf('Config value: %s', $value),
                'type' => Suggestion::TYPE_VALUE,
                'priority' => 555,
            ]);
        }

        return true;
    }
}
