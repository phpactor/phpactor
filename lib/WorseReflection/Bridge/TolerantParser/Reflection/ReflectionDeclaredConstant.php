<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node\ConstElement;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Reflection\ReflectionDeclaredConstant as PhpactorReflectionDeclaredConstant;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Reflection\ReflectionConstant as CoreReflectionConstant;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\TypeFactory;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Phpactor\WorseReflection\TypeUtil;

class ReflectionDeclaredConstant extends AbstractReflectedNode implements PhpactorReflectionDeclaredConstant
{
    private ServiceLocator $serviceLocator;

    private ReflectionClassLike $class;

    /**
     * @var Node\Expression\CallExpression
     */
    private CallExpression $node;

    public function __construct(
        ServiceLocator $serviceLocator,
        ReflectionClassLike $class,
        CallExpression $node
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->node = $node;
        $this->class = $class;
    }

    public function name(): string
    {
        return (string)$this->node->getName();
    }

    public function type(): Type
    {
    }

    protected function node(): Node
    {
        return $this->node;
    }

    protected function serviceLocator(): ServiceLocator
    {
        return $this->serviceLocator;
    }
}
