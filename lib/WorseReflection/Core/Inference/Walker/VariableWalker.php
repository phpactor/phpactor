<?php

namespace Phpactor\WorseReflection\Core\Inference\Walker;

use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Variable as WorseVariable;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\Variable;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockFactory;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockVar;
use Phpactor\WorseReflection\Core\Inference\Variable as PhpactorVariable;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope as PhpactorReflectionScope;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\TypeResolver\DefaultTypeResolver;

class VariableWalker extends AbstractWalker
{
    private DocBlockFactory $docblockFactory;
    
    /**
     * @var array<string,Type>
     */
    private array $injectedTypes = [];
    
    public function __construct(
        DocBlockFactory $docblockFactory
    ) {
        $this->docblockFactory = $docblockFactory;
    }

    
    public function nodeFqns(): array
    {
        return [];
    }

    public function walk(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        $scope = new ReflectionScope($resolver->reflector(), $node);
        $docblockType = $this->injectVariablesFromComment($scope, $frame, $node);

        if (!$node instanceof Variable) {
            return $frame;
        }

        $token = $node->name;
        if (false === $token instanceof Token) {
            return $frame;
        }

        $context = NodeContextFactory::create(
            (string)$token->getText($node->getFileContents()),
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
            ]
        );

        $symbolName = $context->symbol()->name();

        if (!isset($this->injectedTypes[$symbolName]) && !($docblockType->isDefined())) {
            return $frame;
        }

        if (isset($this->injectedTypes[$symbolName])) {
            $docblockType = $this->injectedTypes[$symbolName];
            unset($this->injectedTypes[$symbolName]);
        }

        $context = $context->withType($docblockType);
        $locals = $frame->locals();
        foreach ($locals->byName($symbolName)->equalTo($context->symbol()->position()->start()) as $existing) {
            assert($existing instanceof PhpactorVariable);
            // TODO: not sure this will work as expected
            $locals->replace($existing, $existing->withType($context->type()));
            return $frame;
        }
        $frame->locals()->add(WorseVariable::fromSymbolContext($context));

        return $frame;
    }

    private function injectVariablesFromComment(PhpactorReflectionScope $scope, Frame $frame, Node $node): Type
    {
        $comment = $node->getLeadingCommentAndWhitespaceText();
        $docblock = $this->docblockFactory->create(new DefaultTypeResolver($scope), $comment);

        if (false === $docblock->isDefined()) {
            return TypeFactory::undefined();
        }

        $vars = $docblock->vars();
        $resolvedTypes = [];

        /** @var DocBlockVar $var */
        foreach ($docblock->vars() as $var) {
            $type = $var->type();

            if (empty($var->name())) {
                return $type;
            }

            $this->injectedTypes[ltrim($var->name(), '$')] = $type;
        }

        return TypeFactory::undefined();
    }
}
