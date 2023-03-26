<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection as TolerantReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction as CoreReflectionFunction;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Core\NodeText;
use Microsoft\PhpParser\Node\Statement\FunctionDeclaration;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Reflection\TypeResolver\FunctionReturnTypeResolver;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class ReflectionFunction extends AbstractReflectedNode implements CoreReflectionFunction
{
    public function __construct(
        private TextDocument $sourceCode,
        private ServiceLocator $serviceLocator,
        private FunctionDeclaration $node
    ) {
    }

    public function name(): Name
    {
        return Name::fromParts($this->node->getNamespacedName()->getNameParts());
    }

    public function frame(): Frame
    {
        return $this->serviceLocator->frameBuilder()->build($this->node());
    }

    public function docblock(): DocBlock
    {
        return $this->serviceLocator->docblockFactory()->create(
            $this->node()->getLeadingCommentAndWhitespaceText(),
            $this->scope()
        );
    }

    public function inferredType(): Type
    {
        return (new FunctionReturnTypeResolver($this))->resolve();
    }

    public function type(): Type
    {
        $type = NodeUtil::typeFromQualfiedNameLike(
            $this->serviceLocator->reflector(),
            $this->node,
            $this->node->returnTypeList
        );

        if ($this->node->questionToken) {
            return TypeFactory::nullable($type);
        }

        return $type;
    }

    public function parameters(): ReflectionParameterCollection
    {
        return TolerantReflectionParameterCollection::fromFunctionDeclaration($this->serviceLocator, $this->node, $this);
    }

    public function body(): NodeText
    {
        return NodeText::fromString($this->node->__toString());
    }

    public function sourceCode(): TextDocument
    {
        return $this->sourceCode;
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
