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
use Phpactor\WorseReflection\Core\Inference\Variable as PhpactorVariable;
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

    public function enter(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        $scope = new ReflectionScope($resolver->reflector(), $node);
        $docblockType = $this->injectVariablesFromComment($scope, $frame, $node);

        if (null === $docblockType) {
            return $frame;
        }

        if (!$node instanceof Variable) {
            return $frame;
        }

        $token = $node->name;
        if (false === $token instanceof Token) {
            return $frame;
        }

        $name = (string)$token->getText($node->getFileContents());
        $frame->varDocBuffer()->set($name, $docblockType);

        return $frame;
    }

    public function exit(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        return $frame;
    }

    private function injectVariablesFromComment(PhpactorReflectionScope $scope, Frame $frame, Node $node): ?Type
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

            // there's a chance this will be redefined later, but define it now
            // to ensure that type assertions can find a previous variable
            $frame->locals()->add(new PhpactorVariable(
                name: $var->name(),
                offset: $node->getStartPosition(),
                type: $type,
                wasAssigned: false /** $wasAssigned bool */,
                wasDefined: true /** $wasDefined bool */
            ), $node->getStartPosition());
            $frame->varDocBuffer()->set('$' . $var->name(), $type);
        }

        return null;
    }
}
