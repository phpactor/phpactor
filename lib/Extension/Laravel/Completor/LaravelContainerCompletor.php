<?php

namespace Phpactor\Extension\Laravel\Completor;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
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

class LaravelContainerCompletor implements TolerantCompletor
{
    const CONTAINER_CLASSES = [
        'Illuminate\\Contracts\\Foundation\\Application',
        'Illuminate\\Support\\Facades\\App'
    ];

    const CONTAINER_FUNC = ['app'];

    const CONTAINER_METHODS = ['make', 'get'];

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

        $content = $node->argumentExpressionList->getFirstChildNode(ArgumentExpression::class);

        $memberAccess = $node->callableExpression;

        if (
            !$memberAccess instanceof ScopedPropertyAccessExpression &&
            !$memberAccess instanceof MemberAccessExpression
        ) {
            return;
        }

        $methodName = NodeUtil::nameFromTokenOrNode($node, $memberAccess->memberName);

        if (!in_array($methodName, ['get', 'make'])) {
            return;
        }

        $position = $memberAccess instanceof MemberAccessExpression ?
            $memberAccess->dereferencableExpression->getEndPosition() :
            $memberAccess->scopeResolutionQualifier->getEndPosition();

        $containerType = $this->reflector->reflectOffset($source, $position)->symbolContext()->type();

        $isContainerClass = false;

        foreach (self::CONTAINER_CLASSES as $containerClass) {
            if ($containerType instanceof UnionType) {
                if (!$containerType->accepts(TypeFactory::class($containerClass))->isFalseOrMaybe()) {
                    $isContainerClass = true;
                    break;
                }
            }
            elseif (!$containerType->instanceof(TypeFactory::class($containerClass))->isFalseOrMaybe()) {
                $isContainerClass = true;
                break;
            }
        }

        if (!$isContainerClass) {
            return;
        }

        foreach ($this->inspector->services() as $short => $service) {
            $label = $service;
            $suggestion = $inQuote ? $short : sprintf('\'%s\'', $service);
            $import = null;

            if (!$inQuote) {
                $suggestion = '\\' . $service . '::class';
                $label = $service . '::class';
                $import = $service;
            }

            yield Suggestion::createWithOptions($suggestion, [
                'label' => $short,
                'short_description' => $service,
                'documentation' => sprintf('**Laravel Service**: %s', $service),
                'type' => Suggestion::TYPE_VALUE,
                'name_import' => $inQuote ? '' : $import,
                'priority' => 555,
            ]);
        }

        return true;
    }
}
