<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\StringLiteral;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\Reflection\ReflectionDeclaredConstant as PhpactorReflectionDeclaredConstant;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\ServiceLocator;

class ReflectionDeclaredConstant extends AbstractReflectedNode implements PhpactorReflectionDeclaredConstant
{
    private ServiceLocator $serviceLocator;

    private StringLiteral $name;

    private ArgumentExpression $value;

    private SourceCode $sourceCode;

    public function __construct(
        ServiceLocator $serviceLocator,
        SourceCode $sourceCode,
        StringLiteral $name,
        ArgumentExpression $value
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->name = $name;
        $this->value = $value;
        $this->sourceCode = $sourceCode;
    }

    public function name(): Name
    {
        return Name::fromString($this->name->getStringContentsText());
    }

    public function type(): Type
    {
        return $this->serviceLocator->symbolContextResolver()->resolveNode(new Frame(''), $this->value)->type();
    }

    protected function node(): Node
    {
        return $this->name;
    }

    protected function serviceLocator(): ServiceLocator
    {
        return $this->serviceLocator;
    }

    public function sourceCode(): SourceCode
    {
        return $this->sourceCode;
    }
}
