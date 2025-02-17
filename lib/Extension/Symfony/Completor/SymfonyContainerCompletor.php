<?php

namespace Phpactor\Extension\Symfony\Completor;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ArrayElement;
use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Microsoft\PhpParser\Node\DelimitedList\ArrayElementList;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;
use Microsoft\PhpParser\Node\Expression\BracedExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\ListIntrinsicExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ParenthesizedExpression;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\StringLiteral;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Suggestion;
// use Phpactor\Extension\Symfony\Model\SymfonyCommandRunner;
use Phpactor\Extension\Symfony\Model\FormTypeCompletionCache;
use Phpactor\Extension\Symfony\Model\SymfonyCommandRunner;
use Phpactor\Extension\Symfony\Model\SymfonyContainerInspector;
use Phpactor\Extension\Symfony\Model\SymfonyContainerService;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ClassStringType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\Reflector;
use classObj;

class SymfonyContainerCompletor implements TolerantCompletor
{
    const CONTAINER_CLASS = 'Symfony\\Component\\DependencyInjection\\ContainerInterface';
    const FORM_BUILDER_INTERFACE = 'Symfony\\Component\\Form\\FormBuilderInterface';

    public function __construct(private Reflector $reflector, private SymfonyContainerInspector $inspector)
    {
    }

    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        yield from $this->completeFormTypeOptions($node, $source, $offset);

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

        if (!$memberAccess instanceof MemberAccessExpression) {
            return;
        }

        $methodName = NodeUtil::nameFromTokenOrNode($node, $memberAccess->memberName);

        if ($methodName !== 'get') {
            return;
        }

        $expression = $memberAccess->dereferencableExpression;
        $containerType = $this->reflector->reflectOffset($source, $expression->getEndPosition())->nodeContext()->type();

        if ($containerType->instanceof(TypeFactory::class(self::CONTAINER_CLASS))->isFalseOrMaybe()) {
            return;
        }

        foreach ($this->inspector->services() as $service) {
            $label = $service->id;
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
                $label = $service->type->short() . '::class';
                $import = $service->type->__toString();
            }

            yield Suggestion::createWithOptions($suggestion, [
                'label' => $label,
                'short_description' => $service->id,
                'documentation' => sprintf('**Symfony Service**: %s', $service->type->__toString()),
                'type' => Suggestion::TYPE_VALUE,
                'name_import' => $import,
                'priority' => 555,
            ]);
        }

        return true;
    }

    private function completeFormTypeOptions(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        if(!($node instanceof StringLiteral)) {
            return;
        }

        if(!($node->parent instanceof ArrayElement)) {
            return;
        }

        $arrayElementNode = $node->parent;

        if (!($arrayElementNode->parent instanceof ArrayElementList)) {
            return;
        }

        $arrayElementList = $arrayElementNode->parent;

        $callNode = $node->getFirstAncestor(CallExpression::class);

        $argumentListNode = $callNode->getFirstDescendantNode(ArgumentExpressionList::class);

        $memberAccess = $callNode->callableExpression;

        if (!$memberAccess instanceof MemberAccessExpression) {
            return;
        }

        $methodName = NodeUtil::nameFromTokenOrNode($callNode, $memberAccess->memberName);

        if ($methodName !== 'add') {
            return;
        }

        $expression = $memberAccess->dereferencableExpression;
        $containerType = $this->reflector->reflectOffset($source, $expression->getEndPosition())->nodeContext()->type();

        if ($containerType->instanceof(TypeFactory::class(self::FORM_BUILDER_INTERFACE))->isFalseOrMaybe()) {
            return;
        }

        // get second argument

        $generator = $argumentListNode->getChildNodes();
        $generator->next();

        $formTypeNode = $generator->current();

        if(!($formTypeNode instanceof ArgumentExpression)) {
            return;
        }

        $formTypeClassType = $this->reflector->reflectOffset($source, $formTypeNode->getEndPosition())->nodeContext()->type();

        if(!($formTypeClassType instanceof ClassStringType)) {
            return;
        }

        $formTypeClassFQN = $formTypeClassType->className()->full();

        yield from FormTypeCompletionCache::complete($formTypeClassFQN);

        // $result = SymfonyCommandRunner::run('debug:form --format json', $formTypeClassFQN);

        // $data = json_decode($result, true);

        // $options = $data['options'] ?? [];
        // $ownOptions = $options['own'] ?? [];
        // $parentOptions = $options['parent'] ?? [];

        // foreach ($ownOptions as $option) {
        //     yield Suggestion::createWithOptions($option, [
        //         'label' => $option,
        //         'short_description' => $option,
        //         'documentation' => $option,
        //         'type' => Suggestion::TYPE_CONSTANT,
        //         'priority' => 555,
        //     ]);
        // }

        // foreach($parentOptions as $parentType => $options) {
        //     foreach ($options as $option) {
        //         yield Suggestion::createWithOptions($option, [
        //             'label' => $option,
        //             'short_description' => $parentType,
        //             'documentation' => $parentType,
        //             'type' => Suggestion::TYPE_CONSTANT,
        //             'priority' => 555,
        //         ]);
        //     }
        // }

        return true;
    }

    private function serviceIdIsFqn(SymfonyContainerService $service): bool
    {
        return $service->type->isClass() && $service->id === $service->type->__toString();
    }
}
