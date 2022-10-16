<?php

namespace Phpactor\Extension\Symfony\Completor;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Range;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Extension\Symfony\Model\SymfonyContainerInspector;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\Reflector;

class SymfonyContainerCompletor implements TolerantCompletor
{
    private Reflector $reflector;

    private SymfonyContainerInspector $inspector;


    public function __construct(Reflector $reflector, SymfonyContainerInspector $inspector)
    {
        $this->reflector = $reflector;
        $this->inspector = $inspector;
    }

    public function complete(Node $containerType, TextDocument $source, ByteOffset $offset): Generator
    {
        if (!$containerType instanceof CallExpression) {
            return;
        }

        $memberAccess = $containerType->callableExpression;

        if (!$memberAccess instanceof MemberAccessExpression) {
            return;
        }

        $methodName = NodeUtil::nameFromTokenOrNode($containerType, $memberAccess->memberName);

        if ($methodName !== 'get') {
            return;
        }

        $expression = $memberAccess->dereferencableExpression;
        $containerType = $this->reflector->reflectOffset($source, $expression->getStartPosition())->symbolContext()->type();

        if ($containerType->instanceof(TypeFactory::class('Symfony\Component\DependencyInjection\ContainerInterface'))->isFalseOrMaybe()) {
            return;
        }

        foreach ($this->inspector->services() as $service) {
            yield Suggestion::createWithOptions($service->id, [
                'label' => $service->id,
                'short_description' => $service->type->__toString(),
                'type' => Suggestion::TYPE_VALUE,
            ]);
        }

        return true;
    }
}
