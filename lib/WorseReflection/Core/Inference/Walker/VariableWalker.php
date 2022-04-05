<?php

namespace Phpactor\WorseReflection\Core\Inference\Walker;

use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Variable as WorseVariable;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\Variable;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockFactory;
use Phpactor\WorseReflection\Core\Inference\NodeToTypeConverter;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockVar;
use Phpactor\WorseReflection\Core\Inference\Variable as PhpactorVariable;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Types;

class VariableWalker extends AbstractWalker
{
    private DocBlockFactory $docblockFactory;
    
    /**
     * @var array<string,Types>
     */
    private array $injectedTypes = [];
    
    private NodeToTypeConverter $nameResolver;

    public function __construct(
        DocBlockFactory $docblockFactory,
        NodeToTypeConverter $nameResolver
    ) {
        $this->docblockFactory = $docblockFactory;
        $this->nameResolver = $nameResolver;
    }

    
    public function nodeFqns(): array
    {
        return [];
    }

    public function walk(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        $docblockTypes = $this->injectVariablesFromComment($frame, $node);

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

        if (!isset($this->injectedTypes[$symbolName]) && $docblockTypes->count() === 0) {
            return $frame;
        }

        if (isset($this->injectedTypes[$symbolName])) {
            $docblockTypes = $this->injectedTypes[$symbolName];
            unset($this->injectedTypes[$symbolName]);
        }

        $context = $context->withType($docblockTypes->best());
        $locals = $frame->locals();
        foreach ($locals->byName($symbolName)->equalTo($context->symbol()->position()->start()) as $existing) {
            assert($existing instanceof PhpactorVariable);
            // TODO: not sure this will work as expected
            $locals->replace($existing, $existing->withType($context->type()));
            return $frame;
        }
        $frame->locals()->add($context->symbol()->position()->start(), WorseVariable::fromSymbolContext($context));

        return $frame;
    }

    private function injectVariablesFromComment(Frame $frame, Node $node): Types
    {
        $comment = $node->getLeadingCommentAndWhitespaceText();
        $docblock = $this->docblockFactory->create($comment);

        if (false === $docblock->isDefined()) {
            return Types::empty();
        }

        $vars = $docblock->vars();
        $resolvedTypes = [];

        /** @var DocBlockVar $var */
        foreach ($docblock->vars() as $var) {
            $resolvedTypes = Types::fromTypes(array_map(function (Type $type) use ($node) {
                return $this->nameResolver->resolve(
                    $node,
                    $type
                );
            }, iterator_to_array($var->types())));

            if (empty($var->name())) {
                return $resolvedTypes;
            }

            $this->injectedTypes[ltrim($var->name(), '$')] = $resolvedTypes;
        }

        return Types::empty();
    }
}
