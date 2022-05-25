<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionTraitCollection as PhpactorReflectionTraitCollection;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TraitImport\TraitImports;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\Collection\ChainReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection as CoreReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection as CoreReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionTraitCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait as CoreReflectionTrait;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\TypeResolver\ClassLikeTypeResolver;

class ReflectionTrait extends AbstractReflectionClass implements CoreReflectionTrait
{
    private ServiceLocator $serviceLocator;
    
    private TraitDeclaration $node;
    
    private SourceCode $sourceCode;

    public function __construct(
        ServiceLocator $serviceLocator,
        SourceCode $sourceCode,
        TraitDeclaration $node
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->node = $node;
        $this->sourceCode = $sourceCode;
    }

    public function methods(ReflectionClassLike $contextClass = null): CoreReflectionMethodCollection
    {
        $contextClass = $contextClass ?: $this;
        $methods = ReflectionMethodCollection::fromTraitDeclaration($this->serviceLocator, $this->node, $contextClass);
        $traitImports = TraitImports::forTraitDeclaration($this->node);
        $traitMethods = $this->resolveTraitMethods($traitImports, $contextClass, $this->traits());

        return $methods->merge($traitMethods);
    }

    /**
     * @return ReflectionMemberCollection<ReflectionMember>
     */
    public function members(): ReflectionMemberCollection
    {
        return ChainReflectionMemberCollection::fromCollections([
            $this->properties(),
            $this->methods()
        ]);
    }

    public function properties(): CoreReflectionPropertyCollection
    {
        $properties = ReflectionPropertyCollection::fromTraitDeclaration($this->serviceLocator, $this->node, $this);

        return $properties;
    }

    public function name(): ClassName
    {
        return ClassName::fromString((string) $this->node()->getNamespacedName());
    }

    public function sourceCode(): SourceCode
    {
        return $this->sourceCode;
    }

    public function isInstanceOf(ClassName $className): bool
    {
        if ($className == $this->name()) {
            return true;
        }

        return false;
    }

    public function docblock(): DocBlock
    {
        return $this->serviceLocator->docblockFactory()->create(
            new ClassLikeTypeResolver($this),
            $this->node()->getLeadingCommentAndWhitespaceText()
        );
    }

    public function traits(): ReflectionTraitCollection
    {
        return PhpactorReflectionTraitCollection::fromTraitDeclaration($this->serviceLocator, $this->node);
    }
    /**
     * @return TraitDeclaration
     */
    protected function node(): Node
    {
        return $this->node;
    }

    protected function serviceLocator(): ServiceLocator
    {
        return $this->serviceLocator;
    }
}
