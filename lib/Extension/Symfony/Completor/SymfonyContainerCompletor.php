<?php

namespace Phpactor\Extension\Symfony\Completor;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Range;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Extension\Symfony\Model\SymfonyContainerInspector;
use Phpactor\Extension\Symfony\Model\SymfonyContainerService;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\Reflector;

class SymfonyContainerCompletor implements TolerantCompletor
{
    const CONTAINER_CLASS = 'Symfony\\Component\\DependencyInjection\\ContainerInterface';

    private Reflector $reflector;

    private SymfonyContainerInspector $inspector;

    public function __construct(Reflector $reflector, SymfonyContainerInspector $inspector)
    {
        $this->reflector = $reflector;
        $this->inspector = $inspector;
    }

    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        if ($node instanceof SourceFileNode || $node instanceof CompoundStatementNode) {
            $node = NodeUtil::firstDescendantNodeBeforeOffset($node, $offset->toInt());
        }

        $memberAccessExpression = null;
        $inQuote = false;
        if (!$node instanceof CallExpression) {
            if ($node->parent instanceof MemberAccessExpression) {
                $memberAccessExpression = $node->parent;
                $inQuote = true;
            } else {
                return;
            }
        }

        $memberAccess = $memberAccessExpression ?: $node->callableExpression;

        if (!$memberAccess instanceof MemberAccessExpression) {
            return;
        }

        $methodName = NodeUtil::nameFromTokenOrNode($node, $memberAccess->memberName);

        if ($methodName !== 'get') {
            return;
        }

        $expression = $memberAccess->dereferencableExpression;
        $containerType = $this->reflector->reflectOffset($source, $expression->getStartPosition())->symbolContext()->type();

        if ($containerType->instanceof(TypeFactory::class(self::CONTAINER_CLASS))->isFalseOrMaybe()) {
            return;
        }

        foreach ($this->inspector->services() as $service) {
            $suggestion = $inQuote ? $service->id : sprintf('\'%s\'', $service->id);
            $import = null;

            if ($this->serviceIdIsFqn($service) && $inQuote) {
                continue;
            }

            if (false === $this->serviceIdIsFqn($service) && false === $inQuote) {
                continue;
            }

            if (false === $inQuote && $this->serviceIdIsFqn($service)) {
                $suggestion = $service->type->short() . '::class';
                $import = $service->type->__toString();
            }

            yield Suggestion::createWithOptions($suggestion, [
                'label' => $service->id,
                'short_description' => $service->id,
                'documentation' => sprintf('**Symfony Service**: %s', $service->type->__toString()),
                'type' => Suggestion::TYPE_VALUE,
                'name_import' => $import,
            ]);
        }

        return true;
    }

    private function serviceIdIsFqn(SymfonyContainerService $service): bool
    {
        return $service->type->isClass() && $service->id === $service->type->__toString();
    }
}
