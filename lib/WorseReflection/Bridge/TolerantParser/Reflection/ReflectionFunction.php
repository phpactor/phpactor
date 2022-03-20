<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionParameterCollection as TolerantReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction as CoreReflectionFunction;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Core\NodeText;
use Microsoft\PhpParser\Node\Statement\FunctionDeclaration;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\Core\Reflection\TypeResolver\FunctionReturnTypeResolver;
use Phpactor\WorseReflection\Core\Util\QualifiedNameListUtil;

class ReflectionFunction extends AbstractReflectedNode implements CoreReflectionFunction
{
    private ServiceLocator $serviceLocator;
    
    private FunctionDeclaration $node;
    
    private SourceCode $sourceCode;

    public function __construct(SourceCode $sourceCode, ServiceLocator $serviceLocator, FunctionDeclaration $node)
    {
        $this->serviceLocator = $serviceLocator;
        $this->node = $node;
        $this->sourceCode = $sourceCode;
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
        return $this->serviceLocator->docblockFactory()->create($this->node()->getLeadingCommentAndWhitespaceText());
    }

    public function inferredTypes(): Types
    {
        return (new FunctionReturnTypeResolver($this))->resolve();
    }

    public function type(): Type
    {
        $type = QualifiedNameListUtil::firstQualifiedNameOrToken($this->node->returnTypeList);

        if (null === $type) {
            return TypeFactory::unknown();
        }

        if ($type instanceof Token) {
            return TypeFactory::fromStringWithReflector(
                (string)$type->getText($this->node->getFileContents()),
                $this->serviceLocator()->reflector()
            );
        }

        if (!$type instanceof QualifiedName) {
            return TypeFactory::unknown();
        }

        return TypeFactory::fromStringWithReflector($type->getResolvedName(), $this->serviceLocator()->reflector());
    }

    public function parameters(): ReflectionParameterCollection
    {
        return TolerantReflectionParameterCollection::fromFunctionDeclaration($this->serviceLocator, $this->node, $this);
    }

    public function body(): NodeText
    {
    }

    public function sourceCode(): SourceCode
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
