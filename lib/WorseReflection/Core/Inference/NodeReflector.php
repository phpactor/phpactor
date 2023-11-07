<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Attribute;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionAttribute;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionMethodCall;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionObjectCreationExpression as PhpactorReflectionObjectCreationExpression;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionStaticMemberAccess;
use Phpactor\WorseReflection\Core\Exception\CouldNotResolveNode;
use Phpactor\WorseReflection\Core\Reflection\ReflectionNode;
use Phpactor\WorseReflection\Core\Reflection\ReflectionObjectCreationExpression;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionStaticMethodCall;

class NodeReflector
{
    public function __construct(private ServiceLocator $services)
    {
    }

    public function reflectNode(Frame $frame, Node $node): ReflectionNode
    {
        if ($node instanceof MemberAccessExpression) {
            return $this->reflectMemberAccessExpression($frame, $node);
        }

        if ($node instanceof ScopedPropertyAccessExpression) {
            return $this->reflectScopedPropertyAccessExpression($frame, $node);
        }

        if ($node instanceof ObjectCreationExpression) {
            return $this->reflectObjectCreationExpression($frame, $node);
        }

        if ($node->parent instanceof Attribute) {
            return $this->reflectAttribute($frame, $node->parent);
        }

        throw new CouldNotResolveNode(sprintf(
            'Did not know how to reflect node of type "%s"',
            get_class($node)
        ));
    }

    private function reflectScopedPropertyAccessExpression(Frame $frame, ScopedPropertyAccessExpression $node): ReflectionStaticMemberAccess|ReflectionStaticMethodCall
    {
        if ($node->parent instanceof CallExpression) {
            return $this->reflectStaticMethodCall($frame, $node);
        }

        return $this->reflectCaseOrConstant($frame, $node);
    }

    private function reflectMemberAccessExpression(Frame $frame, MemberAccessExpression $node): ReflectionMethodCall
    {
        if ($node->parent instanceof CallExpression) {
            return $this->reflectMethodCall($frame, $node);
        }
        throw new CouldNotResolveNode(sprintf(
            'Did not know how to reflect node of type "%s"',
            get_class($node)
        ));
    }

    private function reflectMethodCall(Frame $frame, MemberAccessExpression $node): ReflectionMethodCall
    {
        return new ReflectionMethodCall(
            $this->services,
            $frame,
            $node
        );
    }

    private function reflectStaticMethodCall(Frame $frame, ScopedPropertyAccessExpression $node): ReflectionStaticMethodCall
    {
        return new ReflectionStaticMethodCall(
            $this->services,
            $frame,
            $node
        );
    }

    private function reflectObjectCreationExpression(Frame $frame, ObjectCreationExpression $node): ReflectionObjectCreationExpression
    {
        return new PhpactorReflectionObjectCreationExpression(
            $this->services,
            $frame,
            $node
        );
    }

    private function reflectAttribute(Frame $frame, Attribute $node): ReflectionNode
    {
        return new ReflectionAttribute(
            $this->services,
            $frame,
            $node
        );
    }

    private function reflectCaseOrConstant(Frame $frame, ScopedPropertyAccessExpression $node): ReflectionStaticMemberAccess
    {
        return new ReflectionStaticMemberAccess(
            $this->services,
            $frame,
            $node
        );
    }
}
