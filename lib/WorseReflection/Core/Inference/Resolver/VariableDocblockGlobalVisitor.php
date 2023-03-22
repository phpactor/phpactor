<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionScope;
use Microsoft\PhpParser\Node\Expression\Variable;

use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;

class VariableDocblockGlobalVisitor implements Resolver
{
    public function resolve(NodeContextResolver $resolver, NodeContext $context, Node $node): NodeContext
    {
        $scope = new ReflectionScope($resolver->reflector(), $node);
        $docblockType = $this->injectVariablesFromComment($resolver, $scope, $context, $node);

        if (null === $docblockType) {
            return $context;
        }

        if (!$node instanceof Variable) {
            return $context;
        }

        $token = $node->name;
        if (false === $token instanceof Token) {
            return $context;
        }

        $name = (string)$token->getText($node->getFileContents());
        $context->frame()->varDocBuffer()->set($name, $docblockType);

        return $context;
    }

    private function injectVariablesFromComment(NodeContextResolver $resolver, ReflectionScope $scope, NodeContext $context, Node $node): ?Type
    {
        $comment = $node->getLeadingCommentAndWhitespaceText();
        $docblock = $resolver->docblockFactory()->create($comment, $scope);

        if (false === $docblock->isDefined()) {
            return null;
        }

        $vars = $docblock->vars();
        $resolvedTypes = [];

        foreach ($docblock->vars() as $var) {
            $type = $var->type();

            if (empty($var->name())) {
                return $type;
            }

            $context->frame()->varDocBuffer()->set('$' . $var->name(), $type);
        }
        return null;
    }
}
