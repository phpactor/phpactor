<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Phpactor\WorseReflection\Core\ClassHierarchyResolver;
use Phpactor\WorseReflection\Core\Reflection\Collection\ClassLikeReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionTraitCollection as PhpactorReflectionTraitCollection;
use Phpactor\WorseReflection\Core\ClassName;
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

    /**
     * @var array<string,bool>
     */
    private array $visited;

    private ?ClassLikeReflectionMemberCollection $ownMembers = null;

    private ?ClassLikeReflectionMemberCollection $members = null;

    /**
     * @param array<string,bool> $visited
     */
    public function __construct(
        ServiceLocator $serviceLocator,
        SourceCode $sourceCode,
        TraitDeclaration $node,
        array $visited = []
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->node = $node;
        $this->sourceCode = $sourceCode;
        $this->visited = $visited;
    }

    public function methods(ReflectionClassLike $contextClass = null): CoreReflectionMethodCollection
    {
        return $this->members()->methods();
    }

    /**
     * @return ReflectionMemberCollection<ReflectionMember>
     */
    public function members(): ReflectionMemberCollection
    {
        if ($this->members) {
            return $this->members;
        }
        $members = ClassLikeReflectionMemberCollection::empty();
        foreach ((new ClassHierarchyResolver())->resolve($this) as $reflectionClassLike) {
            /** @phpstan-ignore-next-line Constants is compatible with this */
            $members = $members->merge($reflectionClassLike->ownMembers());
        }

        $this->members = $members->map(fn (ReflectionMember $member) => $member->withClass($this));
        return $this->members;
    }

    public function ownMembers(): ReflectionMemberCollection
    {
        if ($this->ownMembers) {
            return $this->ownMembers;
        }
        $this->ownMembers = ClassLikeReflectionMemberCollection::fromTraitMemberDeclarations(
            $this->serviceLocator,
            $this->node,
            $this
        );
        return $this->ownMembers;
    }

    public function properties(): CoreReflectionPropertyCollection
    {
        return $this->members()->properties();
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
            $this->node()->getLeadingCommentAndWhitespaceText()
        )->withTypeResolver(new ClassLikeTypeResolver($this));
    }

    public function traits(): ReflectionTraitCollection
    {
        return PhpactorReflectionTraitCollection::fromTraitDeclaration($this->serviceLocator, $this->node, $this->visited);
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
