<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassBaseClause;
use Microsoft\PhpParser\Node\ClassInterfaceClause;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\TokenKind;

use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TraitImport\TraitImports;
use Phpactor\WorseReflection\Core\ClassHierarchyResolver;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\Collection\ClassLikeReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionClassCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionClassLikeCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionConstantCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionInterfaceCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionTraitCollection;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass as CoreReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;

class ReflectionClass extends AbstractReflectionClass implements CoreReflectionClass
{
    private ?ReflectionInterfaceCollection $interfaces = null;

    private ?CoreReflectionClass $parent = null;

    private ?ReflectionClassCollection $ancestors = null;

    private ?ReflectionTraitCollection $traits = null;

    private ?ClassLikeReflectionMemberCollection $ownMembers = null;

    private ?ClassName $name = null;

    private ?ClassLikeReflectionMemberCollection $members = null;

    /**
     * @param array<string,bool> $visited
     */
    public function __construct(
        private ServiceLocator $serviceLocator,
        private TextDocument $sourceCode,
        private ClassDeclaration $node,
        private array $visited = []
    ) {
    }

    public function isAbstract(): bool
    {
        $modifier = $this->node->abstractOrFinalModifier;

        /** @phpstan-ignore-next-line */
        if (!$modifier) {
            return false;
        }

        return $modifier->kind === TokenKind::AbstractKeyword;
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
        foreach ($this->hierarchy() as $reflectionClassLike) {
            $classLikeMembers = $reflectionClassLike->ownMembers();
            $classLikeMembers = $classLikeMembers->merge($this->serviceLocator->methodProviders()->provideMembers(
                $this->serviceLocator,
                $reflectionClassLike
            ));

            // only inerit public and protected properties from parent classes
            if ($reflectionClassLike !== $this && !$reflectionClassLike instanceof ReflectionTrait) {
                $classLikeMembers = $classLikeMembers->byVisibilities([Visibility::public(), Visibility::protected()]);
            }

            // we only take constants from interfaces, methods must be implemented.
            if ($reflectionClassLike instanceof ReflectionInterface) {
                /** @phpstan-ignore-next-line collection IS compatible */
                $members = $members->merge($classLikeMembers->constants());
                /** @phpstan-ignore-next-line collection IS compatible */
                $members = $members->merge($classLikeMembers->virtual());
                continue;
            }

            /** @phpstan-ignore-next-line Constants is compatible with this */
            $members = $members->merge($classLikeMembers);

            // we need to account for traits renaming aliases
            if ($reflectionClassLike instanceof ReflectionTrait) {
                $traitImports = TraitImports::forClassDeclaration($this->node);
                /** @phpstan-ignore-next-line collection IS compatible */
                $members = $members->merge($this->resolveTraitMethods($traitImports, $this, $this->traits()));
                continue;
            }
        }
        $this->members = $members->map(fn (ReflectionMember $member) => $member->withClass($this));

        return $this->members;
    }

    public function ownMembers(): ReflectionMemberCollection
    {
        if ($this->ownMembers) {
            return $this->ownMembers;
        }
        $this->ownMembers = ClassLikeReflectionMemberCollection::fromClassMemberDeclarations(
            $this->serviceLocator,
            $this->node,
            $this
        );
        return $this->ownMembers;
    }

    public function constants(): ReflectionConstantCollection
    {
        return $this->members()->constants();
    }

    public function parent(): ?CoreReflectionClass
    {
        if ($this->parent) {
            return $this->parent;
        }

        /** @phpstan-ignore-next-line */
        if (!$this->node->classBaseClause) {
            return null;
        }

        $baseClass = $this->node->classBaseClause->baseClass;

        // incomplete class
        if (!$baseClass instanceof QualifiedName) {
            return null;
        }

        try {
            $className = ClassName::fromString((string) $this->node->classBaseClause->baseClass->getResolvedName());

            // prevent infinite loops
            if ($className == $this->name()) {
                return null;
            }

            $reflectedClass = $this->serviceLocator->reflector()->reflectClassLike(
                $className,
                $this->visited,
            );

            if (!$reflectedClass instanceof CoreReflectionClass) {
                $this->serviceLocator->logger()->warning(sprintf(
                    'Class cannot extend interface. Class "%s" extends interface or trait "%s"',
                    $this->name(),
                    $reflectedClass->name()
                ));
                return null;
            }

            $this->parent = $reflectedClass;

            return $reflectedClass;
        } catch (NotFound) {
            return null;
        }
    }

    public function properties(ReflectionClassLike $contextClass = null): ReflectionPropertyCollection
    {
        return $this->members()->properties();
    }

    public function methods(ReflectionClassLike $contextClass = null): ReflectionMethodCollection
    {
        return $this->members()->methods();
    }

    public function interfaces(): ReflectionInterfaceCollection
    {
        if ($this->interfaces) {
            return $this->interfaces;
        }

        $parentInterfaces = null;
        foreach ($this->ancestors() as $ancestor) {
            $parentInterfaces = $ancestor->interfaces();
        }

        $interfaces = ReflectionInterfaceCollection::fromClassDeclaration($this->serviceLocator, $this->node);

        if ($parentInterfaces) {
            $interfaces = $parentInterfaces->merge($interfaces);
        }

        foreach ($interfaces as $interface) {
            $interfaces = $interfaces->merge($interface->parents());
        }

        $this->interfaces = $interfaces;

        return $interfaces;
    }

    /**
     * @return ReflectionTraitCollection<ReflectionTrait>
     */
    public function traits(): ReflectionTraitCollection
    {
        if ($this->traits) {
            return $this->traits;
        }
        $parentTraits = null;

        if ($this->parent()) {
            $parentTraits = $this->parent()->traits();
        }

        $traits = ReflectionTraitCollection::fromClassDeclaration($this->serviceLocator, $this->node);

        if ($parentTraits) {
            $traits =  $parentTraits->merge($traits);
        }

        $this->traits = $traits;

        return $traits;
    }

    public function memberListPosition(): ByteOffsetRange
    {
        return ByteOffsetRange::fromInts(
            $this->node->classMembers->openBrace->start,
            $this->node->classMembers->openBrace->start + $this->node->classMembers->openBrace->length
        );
    }

    public function name(): ClassName
    {
        if ($this->name) {
            return $this->name;
        }
        $this->name = ClassName::fromString((string) $this->node->getNamespacedName());
        return $this->name;
    }

    public function isInstanceOf(ClassName $className): bool
    {
        if ($className == $this->name()) {
            return true;
        }

        // do not try and reflect the parents if we can locally see that it is
        // an instance of the given class
        $baseClause = $this->node->classBaseClause;
        if ($baseClause instanceof ClassBaseClause) {
            NodeUtil::qualfiiedNameIs($baseClause->baseClass, $className->__toString());
        }

        // do not try and reflect the parents if we can locally see that it is
        // an instance of the given class
        $baseClause = $this->node->classInterfaceClause;
        if ($baseClause instanceof ClassInterfaceClause) {
            if (NodeUtil::qualifiedNameListContains($baseClause->interfaceNameList, $className->__toString())) {
                return true;
            }
        }

        if ($this->ancestors()->has((string)$className)) {
            return true;
        }

        return $this->interfaces()->has((string) $className);
    }

    public function sourceCode(): TextDocument
    {
        return $this->sourceCode;
    }

    public function isConcrete(): bool
    {
        if (false === $this->isClass()) {
            return false;
        }

        return false === $this->isAbstract();
    }

    public function docblock(): DocBlock
    {
        return $this->serviceLocator->docblockFactory()->create(
            $this->node()->getLeadingCommentAndWhitespaceText(),
            $this->scope()
        );
    }

    public function ancestors(): ReflectionClassCollection
    {
        if ($this->ancestors) {
            return $this->ancestors;
        }
        $ancestors = [];
        $class = $this;

        while ($parent = $class->parent()) {
            if (isset($ancestors[$parent->name()->full()])) {
                unset($ancestors[$parent->name()->full()]);
                break;
            }

            $ancestors[$parent->name()->full()] = $parent;

            $class = $parent;
        }

        $this->ancestors = ReflectionClassCollection::fromReflections($ancestors);
        return $this->ancestors;
    }

    public function isFinal(): bool
    {
        $modifier = $this->node->abstractOrFinalModifier;

        /** @phpstan-ignore-next-line */
        if (!$modifier) {
            return false;
        }

        return $modifier->kind === TokenKind::FinalKeyword;
    }

    public function serviceLocator(): ServiceLocator
    {
        return $this->serviceLocator;
    }

    public function hierarchy(): ReflectionClassLikeCollection
    {
        return ReflectionClassLikeCollection::fromReflections((new ClassHierarchyResolver())->resolve($this));
    }

    public function classLikeType(): string
    {
        return 'class';
    }

    protected function node(): Node
    {
        return $this->node;
    }
}
