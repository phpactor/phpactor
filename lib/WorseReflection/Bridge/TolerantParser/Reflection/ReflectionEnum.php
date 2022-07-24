<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\EnumDeclaration;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\Collection\ClassLikeReflectionMemberCollection;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionEnumCaseCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection as CoreReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection as CoreReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnum as CoreReflectionEnum;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\TypeResolver\ClassLikeTypeResolver;

class ReflectionEnum extends AbstractReflectionClass implements CoreReflectionEnum
{
    private ServiceLocator $serviceLocator;

    private EnumDeclaration $node;

    private SourceCode $sourceCode;

    public function __construct(
        ServiceLocator $serviceLocator,
        SourceCode $sourceCode,
        EnumDeclaration $node
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->node = $node;
        $this->sourceCode = $sourceCode;
    }

    public function methods(ReflectionClassLike $contextClass = null): CoreReflectionMethodCollection
    {
        return $this->members()->methods();
    }

    public function cases(): ReflectionEnumCaseCollection
    {
        return $this->ownMembers()->enumCases();
    }

    /**
     * @return ReflectionMemberCollection<ReflectionMember>
     */
    public function members(): ReflectionMemberCollection
    {
        $members = ClassLikeReflectionMemberCollection::empty();
        $members = $members->merge($this->ownMembers());
        try {
            $enumMethods = $this->serviceLocator()->reflector()->reflectInterface('BackedEnum')->methods($this);
            /** @phpstan-ignore-next-line It is fine */
            return $members->merge($enumMethods)->map(
                fn (ReflectionMember $member) => $member->withClass($this)
            );
        } catch (NotFound $notFound) {
        }

        return $members;
    }

    public function ownMembers(): ReflectionMemberCollection
    {
        return ClassLikeReflectionMemberCollection::fromEnumMemberDeclarations(
            $this->serviceLocator,
            $this->node,
            $this
        );
    }

    public function properties(): CoreReflectionPropertyCollection
    {
        return $this->ownMembers()->properties();
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

    /**
     * @return EnumDeclaration
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
