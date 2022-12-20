<?php

namespace Phpactor\Extension\Laravel\Completor;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\StringLiteral;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Extension\Laravel\Adapter\Laravel\LaravelContainerInspector;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\TypeFactory;
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

        if (!$memberAccess instanceof ScopedPropertyAccessExpression) {
            return;
        }

        $methodName = NodeUtil::nameFromTokenOrNode($node, $memberAccess->memberName);

        if ($methodName !== 'get') {
            return;
        }

        $containerType = $this->reflector->reflectOffset($source, $memberAccess->scopeResolutionQualifier->getEndPosition())->symbolContext()->type();

        $isContainerClass = false;

        foreach (self::CONTAINER_CLASSES as $containerClass) {
            dump('test: '. $containerClass);
            if (!$containerType->instanceof(TypeFactory::class($containerClass))->isFalseOrMaybe()) {
                $isContainerClass = true;
                break;
            }
        }

        if (!$isContainerClass) {
            dump('Not a container class.');
            return;
        }

        foreach ($this->inspector->services() as $service) {
            $label = $service;
            $suggestion = $inQuote ? $service : sprintf('\'%s\'', $service);
            $import = null;

            if (!$inQuote) {
                $suggestion = '\\' . $service . '::class';
                $label = $service . '::class';
                $import = $service;
            }

            yield Suggestion::createWithOptions($suggestion, [
                'label' => $label,
                'short_description' => $service,
                'documentation' => sprintf('**Laravel Service**: %s', $service),
                'type' => Suggestion::TYPE_VALUE,
                'name_import' => $import,
                'priority' => 555,
            ]);
        }

        return true;
    }
}
