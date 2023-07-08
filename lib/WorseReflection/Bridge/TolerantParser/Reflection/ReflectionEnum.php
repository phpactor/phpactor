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
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionTraitCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnum as CoreReflectionEnum;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class ReflectionEnum extends AbstractReflectionClass implements CoreReflectionEnum
{
    private ?ReflectionTraitCollection $traits = null;

    public function __construct(
        private ServiceLocator $serviceLocator,
        private TextDocument $sourceCode,
        private EnumDeclaration $node
    ) {
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
        /** @phpstan-ignore-next-line Constants is compatible with this */
        $members = $members->merge($this->ownMembers());
        foreach ($this->traits() as $trait) {
            /** @phpstan-ignore-next-line Constants is compatible with this */
            $members = $members->merge($trait->members());
        }
        try {
            $enumType = $this->isBacked() ? 'BackedEnum' : 'UnitEnum';
            $enumMethods = $this->serviceLocator()->reflector()->reflectInterface($enumType)->members();
            /** @phpstan-ignore-next-line It is fine */
            return $members->merge($enumMethods)->map(
                fn (ReflectionMember $member) => $member->withClass($this)
            );
        } catch (NotFound) {
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
        return $this->members()->properties();
    }

    public function name(): ClassName
    {
        return ClassName::fromString((string) $this->node()->getNamespacedName());
    }

    public function sourceCode(): TextDocument
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
            $this->node()->getLeadingCommentAndWhitespaceText(),
            $this->scope()
        );
    }

    public function isBacked(): bool
    {
        return $this->node->enumType !== null;
    }

    public function backedType(): Type
    {
        return NodeUtil::typeFromQualfiedNameLike($this->serviceLocator()->reflector(), $this->node, $this->node->enumType);
    }

    public function classLikeType(): string
    {
        return 'enum';
    }

    public function traits(): ReflectionTraitCollection
    {
        if ($this->traits) {
            return $this->traits;
        }

        $traits = ReflectionTraitCollection::fromEnumDeclaration($this->serviceLocator, $this->node);

        $this->traits = $traits;

        return $traits;
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
