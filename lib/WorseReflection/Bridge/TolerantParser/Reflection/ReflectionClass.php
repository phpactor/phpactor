<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassBaseClause;
use Microsoft\PhpParser\Node\ClassInterfaceClause;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\TokenKind;

use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TraitImport\TraitImports;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\Collection\ChainReflectionMemberCollection;
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

    public function __construct(
        ServiceLocator $serviceLocator,
        SourceCode $sourceCode,
        ClassDeclaration $node
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->node = $node;
        $this->sourceCode = $sourceCode;
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
        /** @phpstan-ignore-next-line Pretty sure this is a phpstan bug */
        return ChainReflectionMemberCollection::fromCollections([
            $this->constants(),
            $this->properties(),
            $this->methods()
        ]);
    }

    public function constants(): ReflectionConstantCollection
    {
        $parentConstants = null;
        if ($this->parent()) {
            $parentConstants = $this->parent()->constants();
        }

        $constants = ReflectionConstantCollection::fromClassDeclaration($this->serviceLocator, $this->node, $this);

        if ($parentConstants) {
            return $parentConstants->merge($constants);
        }

        foreach ($this->interfaces() as $interface) {
            $constants = $constants->merge($interface->constants());
        }

        return $constants;
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

    public function properties(ReflectionClassLike $contextClass = null): ReflectionPropertyCollection
    {
        $cacheKey = $contextClass ? (string) $contextClass->name() : '*_null_*';

        if (isset($this->properties[$cacheKey])) {
            return $this->properties[$cacheKey];
        }

        $properties = ReflectionPropertyCollection::empty();
        $contextClass = $contextClass ?: $this;

        if ($this->traits()->count() > 0) {
            foreach ($this->traits() as $trait) {
                $properties = $properties->merge($trait->properties());
            }
        }

        $parent = $this->parent();
        if ($parent) {
            $properties = $properties->merge(
                $parent->properties($contextClass)->byVisibilities([ Visibility::public(), Visibility::protected() ])
            );
        }

        $properties = $properties->merge(ReflectionPropertyCollection::fromClassDeclaration($this->serviceLocator, $this->node, $contextClass));
        $properties = $properties->merge(ReflectionPropertyCollection::fromClassDeclarationConstructorPropertyPromotion($this->serviceLocator, $this->node, $contextClass));

        $this->properties[$cacheKey] = $properties;

        return $properties;
    }

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
