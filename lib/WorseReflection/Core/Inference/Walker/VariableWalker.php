<?php

namespace Phpactor\WorseReflection\Core\Inference\Walker;

use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionScope;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\Variable;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockFactory;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockVar;
use Phpactor\WorseReflection\Core\Inference\FrameStack;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope as PhpactorReflectionScope;
use Phpactor\WorseReflection\Core\Type;

class VariableWalker implements Walker
{
    public function __construct(private DocBlockFactory $docblockFactory)
    {
    }


    public function nodeFqns(): array
    {
        return [];
    }

    public function enter(FrameResolver $resolver, FrameStack $frameStack, Node $node): void
    {
        $scope = new ReflectionScope($resolver->reflector(), $node);
        $docblockType = $this->injectVariablesFromComment($scope, $frameStack, $node);

        if (null === $docblockType) {
            return;
        }

        if (!$node instanceof Variable) {
            return;
        }

        $token = $node->name;
        if (false === $token instanceof Token) {
            return;
        }

        $name = (string)$token->getText($node->getFileContents());
        $frameStack->current()->varDocBuffer()->set($name, $docblockType);

        return;
    }

    public function exit(FrameResolver $resolver, FrameStack $frameStack, Node $node): void
    {
        return;
    }

    private function injectVariablesFromComment(PhpactorReflectionScope $scope, FrameStack $frameStack, Node $node): ?Type
    {
        $comment = $node->getLeadingCommentAndWhitespaceText();
        $docblock = $this->docblockFactory->create($comment, $scope);

        if (false === $docblock->isDefined()) {
            return null;
        }

        $vars = $docblock->vars();
        $resolvedTypes = [];

        /** @var DocBlockVar $var */
        foreach ($docblock->vars() as $var) {
            $type = $var->type();

            if (empty($var->name())) {
                return $type;
            }

            $frameStack->current()->varDocBuffer()->set('$' . $var->name(), $type);
        }

        return null;
    }
}
