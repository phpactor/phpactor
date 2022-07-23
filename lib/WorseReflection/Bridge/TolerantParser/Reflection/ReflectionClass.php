<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassBaseClause;
use Microsoft\PhpParser\Node\ClassInterfaceClause;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\TokenKind;

use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TraitImport\TraitImports;
use Phpactor\WorseReflection\Core\ClassHierarchyResolver;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\Collection\ClassLikeReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionClassCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionConstantCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionInterfaceCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionTraitCollection;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass as CoreReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\TypeResolver\ClassLikeTypeResolver;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;

class ReflectionClass extends AbstractReflectionClass implements CoreReflectionClass
{
    private ServiceLocator $serviceLocator;

    private ClassDeclaration $node;

    private SourceCode $sourceCode;

    private ?ReflectionInterfaceCollection $interfaces = null;

    private ?CoreReflectionClass $parent = null;

    /**
     * @var array<string,ReflectionMethodCollection>
     */
    private array $methods = [];

    /**
     * @var array<string,ReflectionPropertyCollection>
     */
    private array $properties = [];

    private ?ReflectionClassCollection $ancestors = null;

    private ?ReflectionTraitCollection $traits = null;

    /**
     * @var array<string, bool>
     */
    private array $visited;

    /**
     * @param array<string,bool> $visited
     */
    public function __construct(
        ServiceLocator $serviceLocator,
        SourceCode $sourceCode,
        ClassDeclaration $node,
        array $visited = []
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->node = $node;
        $this->sourceCode = $sourceCode;
        $this->visited = $visited;
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
        $members = ClassLikeReflectionMemberCollection::empty();
        foreach ((new ClassHierarchyResolver())->resolve($this) as $reflectionClassLike) {
            $members = $members->merge($reflectionClassLike->ownMembers());
        }

        return $members;
    }

    public function ownMembers(): ReflectionMemberCollection
    {
        return ClassLikeReflectionMemberCollection::fromClassMemberDeclarations(
            $this->serviceLocator,
            $this->node,
            $this
        );
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

        // incomplete class
        /** @phpstan-ignore-next-line */
        if (!$this->node->classBaseClause->baseClass) {
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
        } catch (NotFound $e) {
            return null;
        }
    }

    // todo: switch to use members()
    public function properties(ReflectionClassLike $contextClass = null): ReflectionPropertyCollection
    {
        return $this->members()->properties();
    }

    // todo: switch to use members()
    public function methods(ReflectionClassLike $contextClass = null): ReflectionMethodCollection
    {
        $cacheKey = $contextClass ? (string) $contextClass->name() : '*_null_*';

        if (isset($this->methods[$cacheKey])) {
            return $this->methods[$cacheKey];
        }

        $contextClass = $contextClass ?: $this;
        $methods = ReflectionMethodCollection::empty();
        $traitImports = TraitImports::forClassDeclaration($this->node);
        $traitMethods = $this->resolveTraitMethods($traitImports, $contextClass, $this->traits());
        $methods = $methods->merge($traitMethods);

        if ($this->parent()) {
            $methods = $methods->merge(
                $this->parent()->methods($contextClass)->byVisibilities([ Visibility::public(), Visibility::protected() ])
            );
        }

        $methods = $methods->merge(
            ReflectionMethodCollection::fromClassDeclaration(
                $this->serviceLocator,
                $this->node,
                $contextClass
            )
        );

        $this->methods[$cacheKey] = $methods;

        return $methods;
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

    public function memberListPosition(): Position
    {
        return Position::fromFullStartStartAndEnd(
            $this->node->classMembers->openBrace->fullStart,
            $this->node->classMembers->openBrace->start,
            $this->node->classMembers->openBrace->start + $this->node->classMembers->openBrace->length
        );
    }

    public function name(): ClassName
    {
        return ClassName::fromString((string) $this->node->getNamespacedName());
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
        /** @phpstan-ignore-next-line */
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

    public function sourceCode(): SourceCode
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
            $this->node()->getLeadingCommentAndWhitespaceText()
        )->withTypeResolver(new ClassLikeTypeResolver($this));
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

    protected function node(): Node
    {
        return $this->node;
    }
}
