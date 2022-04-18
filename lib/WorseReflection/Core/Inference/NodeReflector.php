<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionMethodCall;
use Phpactor\WorseReflection\Core\Exception\CouldNotResolveNode;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionStaticMethodCall;

class NodeReflector
{
    private ServiceLocator $services;

    public function __construct(ServiceLocator $services)
    {
        $this->services = $services;
    }

    public function reflectNode(Frame $frame, Node $node)
    {
        if ($node instanceof MemberAccessExpression) {
            return $this->reflectMemberAccessExpression($frame, $node);
        }

        if ($node instanceof ScopedPropertyAccessExpression) {
            return $this->reflectScopedPropertyAccessExpression($frame, $node);
        }

        throw new CouldNotResolveNode(sprintf(
            'Did not know how to reflect node of type "%s"',
            get_class($node)
        ));
    }

    private function reflectScopedPropertyAccessExpression(Frame $frame, ScopedPropertyAccessExpression $node)
    {
        if ($node->parent instanceof CallExpression) {
            return $this->reflectStaticMethodCall($frame, $node);
        }
    }

    private function reflectMemberAccessExpression(Frame $frame, Node $node)
    {
        if ($node->parent instanceof CallExpression) {
            return $this->reflectMethodCall($frame, $node);
        }
    }

    private function reflectMethodCall(Frame $frame, MemberAccessExpression $node)
    {
        return new ReflectionMethodCall(
            $this->services,
            $frame,
            $node
        );
    }

    private function reflectStaticMethodCall(Frame $frame, ScopedPropertyAccessExpression $node)
    {
        return new ReflectionStaticMethodCall(
            $this->services,
            $frame,
            $node
        );
    }
}
