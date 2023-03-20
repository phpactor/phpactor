<?php

namespace Phpactor\WorseReflection\Core\Inference\Walker;

use Microsoft\PhpParser\MissingToken;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ArrowFunctionCreationExpression;
use Microsoft\PhpParser\Node\Expression\Variable as MicrosoftVariable;
use Microsoft\PhpParser\Node\UseVariableName;
use Microsoft\PhpParser\TokenKind;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Microsoft\PhpParser\FunctionLike;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Phpactor\WorseReflection\Core\Inference\FrameStack;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Variable;
use Microsoft\PhpParser\Node\Expression\AnonymousFunctionCreationExpression;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Statement\FunctionDeclaration;
use Microsoft\PhpParser\Node\Parameter;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;

class FunctionLikeWalker implements Walker
{
    public function nodeFqns(): array
    {
        return [
            FunctionDeclaration::class,
            MethodDeclaration::class,
            AnonymousFunctionCreationExpression::class,
            ArrowFunctionCreationExpression::class,
        ];
    }

    public function enter(FrameResolver $resolver, FrameStack $frameStack, Node $node): void
    {
        assert(
            $node instanceof MethodDeclaration ||
            $node instanceof FunctionDeclaration ||
            $node instanceof AnonymousFunctionCreationExpression ||
            $node instanceof ArrowFunctionCreationExpression
        );

        if (!$node instanceof ArrowFunctionCreationExpression) {
            $frameStack->newFrame();
        }

        $this->walkFunctionLike($resolver, $frameStack, $node);

        return;
    }

    public function exit(FrameResolver $resolver, FrameStack $frameStack, Node $node): void
    {
        return;
    }

    /**
     * @param MethodDeclaration|FunctionDeclaration|AnonymousFunctionCreationExpression|ArrowFunctionCreationExpression $node
     */
    private function walkFunctionLike(FrameResolver $resolver, FrameStack $frameStack, FunctionLike $node): void
    {
        $namespace = $node->getNamespaceDefinition();
        $classNode = $node->getFirstAncestor(
            ClassDeclaration::class,
            InterfaceDeclaration::class,
            TraitDeclaration::class
        );

        if ($node instanceof AnonymousFunctionCreationExpression) {
            $this->addAnonymousImports($frameStack->current(), $node);

            // if this is a static anonymous function, set classNode to NULL
            // so that we don't add the class context
            if ($node->staticModifier && $node->staticModifier->kind === TokenKind::StaticKeyword) {
                $classNode = null;
            }
        }

        // works for both closure and class method (we currently ignore binding)
        if ($classNode) {
            $classType = $resolver->resolveNode($frameStack, $classNode)->type();
            $this->addClassContext($node, $classType, $frameStack->current());
        }

        if (null === $node->parameters) {
            return;
        }

        /** @var Parameter $parameterNode */
        foreach ($node->parameters->getElements() as $parameterNode) {
            $parameterName = $parameterNode->variableName->getText($node->getFileContents());

            $nodeContext = $resolver->resolveNode($frameStack, $parameterNode);

            $context = NodeContextFactory::create(
                (string)$parameterName,
                $parameterNode->getStartPosition(),
                $parameterNode->getEndPosition(),
                [
                    'symbol_type' => Symbol::VARIABLE,
                    'type' => $nodeContext->type(),
                ]
            );

            $frameStack->current()->locals()->set(Variable::fromSymbolContext($context));
        }
    }

    private function addAnonymousImports(Frame $frame, AnonymousFunctionCreationExpression $node): void
    {
        $useClause = $node->anonymousFunctionUseClause;

        if (null === $useClause) {
            return;
        }

        $parentFrame = $frame->parent();
        if (null === $parentFrame) {
            return;
        }
        $parentVars = $parentFrame->locals()->lessThanOrEqualTo($node->getStartPosition());

        if (null === $useClause->useVariableNameList) {
            return;
        }

        if ($useClause->useVariableNameList instanceof MissingToken) {
            return;
        }

        foreach ($useClause->useVariableNameList->getElements() as $element) {
            if (!$element instanceof UseVariableName) {
                continue;
            }
            $varName = $element->variableName->getText($node->getFileContents());
            if (!is_string($varName)) {
                $varName = '';
            }

            $variableContext = NodeContextFactory::create(
                $varName,
                $element->getStartPosition(),
                $element->getEndPosition(),
                [
                    'symbol_type' => Symbol::VARIABLE,
                ]
            );
            $varName = $variableContext->symbol()->name();

            // if not in parent scope, then we know nothing about it
            // add it with above context and continue
            // TODO: Do we infer the type hint??
            if (0 === $parentVars->byName($varName)->count()) {
                $frame->locals()->set(Variable::fromSymbolContext($variableContext));
                continue;
            }

            $variable = $parentVars->byName($varName)->last();

            $variableContext = $variableContext
                ->withType($variable->type());

            $frame->locals()->set(Variable::fromSymbolContext($variableContext));
        }
    }

    private function addClassContext(Node $node, Type $classType, Frame $frame): void
    {
        $context = NodeContextFactory::create(
            'this',
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'type' => $classType,
                'symbol_type' => Symbol::VARIABLE,
            ]
        );

        // add this and self
        $frame->locals()->set(Variable::fromSymbolContext($context));

        if (!$classType instanceof ReflectedClassType) {
            return;
        }
        $reflection = $classType->reflectionOrNull();
        if (null === $reflection) {
            return;
        }
        foreach ($reflection->members()->byMemberType(ReflectionMember::TYPE_PROPERTY) as $property) {
            assert($property instanceof ReflectionProperty);
            $frame->properties()->set(new Variable($property->name(), $property->position()->start()->toInt(), $property->inferredType(), $classType));
        }
    }
}
