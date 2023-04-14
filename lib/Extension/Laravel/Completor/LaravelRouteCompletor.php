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

class LaravelRouteCompletor implements TolerantCompletor
{
    public const CONTAINER_CLASSES = [
        'Illuminate\\Routing\\Redirector',
        'Illuminate\\Http\\RedirectResponse'
    ];
    public const CONTAINER_FUNC = ['route'];
    public const CONTAINER_METHODS = ['route'];

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

        $isViewClass = $isFunction;

        if (!$isViewClass) {
            foreach (self::CONTAINER_CLASSES as $containerClass) {
                if ($containerType instanceof UnionType) {
                    foreach ($containerType->allTypes() as $type) {
                        dump($containerClass . '--' . $type->__toString());
                        if ($containerClass === $type->__toString()) {
                            $isViewClass = true;
                            break;
                        }
                    }
                    // Exit asap.
                    if ($isViewClass) {
                        break;
                    }
                } elseif (!$containerType->instanceof(TypeFactory::class($containerClass))->isFalse()) {
                    $isViewClass = true;
                    break;
                }
            }
        }

        if (!$isViewClass) {
            return;
        }

        foreach ($this->inspector->routes() as $route) {
            if ($route['name']) {
                yield Suggestion::createWithOptions($route['name'], [
                    'label' => $route['name'],
                    'short_description' => $route['uri'],
                    'documentation' => sprintf('**Laravel route**: %s', $route['uri']),
                    'type' => Suggestion::TYPE_VALUE,
                    'priority' => 555,
                ]);
            }
        }

        return true;
    }
}
