<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\TokenKind;

use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TraitImport\TraitImport;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TraitImport\TraitImports;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\Collection\ChainReflectionMemberCollection;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionConstantCollection;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionInterfaceCollection;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionTraitCollection;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionClassCollection;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionClassCollection as TolerantReflectionClassCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionConstantCollection as CoreReflectionConstantCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionInterfaceCollection as CoreReflectionInterfaceCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection as CoreReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection as CoreReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionTraitCollection as CoreReflectionTraitCollection;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass as CoreReflectionClass;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Virtual\Collection\VirtualReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionMethod;
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

    /**
     * @var ReflectionInterfaceCollection<ReflectionInterface>
     */
    private ?ReflectionInterfaceCollection $interfaces = null;
    
    private ?ReflectionClassLike $parent = null;

    /**
     * @var array<string,ReflectionMethodCollection>
     */
    private array $methods = [];

    private ?TolerantReflectionClassCollection $ancestors = null;

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
        if (false === $this->node instanceof ClassDeclaration) {
            return false;
        }

        $modifier = $this->node->abstractOrFinalModifier;

        if (!$modifier) {
            return false;
        }

        return $modifier->kind === TokenKind::AbstractKeyword;
    }

    public function members(): ReflectionMemberCollection
    {
        return ChainReflectionMemberCollection::fromCollections([
            $this->constants(),
            $this->properties(),
            $this->methods()
        ]);
    }

    public function constants(): CoreReflectionConstantCollection
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

        if (!$this->node->classBaseClause) {
            return null;
        }

        // incomplete class
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
                $className
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

    public function properties(ReflectionClassLike $contextClass = null): CoreReflectionPropertyCollection
    {
        $properties = ReflectionPropertyCollection::empty($this->serviceLocator);
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

        return $properties;
    }

    public function methods(ReflectionClassLike $contextClass = null): CoreReflectionMethodCollection
    {
        $cacheKey = $contextClass ? (string) $contextClass->name() : '*_null_*';

        if (isset($this->methods[$cacheKey])) {
            return $this->methods[$cacheKey];
        }

        $contextClass = $contextClass ?: $this;
        $methods = ReflectionMethodCollection::empty($this->serviceLocator);

        $traitImports = new TraitImports($this->node);

        /** @var TraitImport $traitImport */
        foreach ($traitImports as $traitImport) {
            try {
                $trait = $this->traits()->get($traitImport->name());
            } catch (NotFound $notFound) {
                continue;
            }

            $traitMethods = [];
            foreach ($trait->methods($contextClass) as $method) {
                if (false === $traitImport->hasAliasFor($method->name())) {
                    $traitMethods[] = $method;
                    continue;
                }

                $traitAlias = $traitImport->getAlias($method->name());
                $virtualMethod = VirtualReflectionMethod::fromReflectionMethod($trait->methods()->get($traitAlias->originalName()))
                    ->withName($traitAlias->newName())
                    ->withVisibility($traitAlias->visiblity($method->visibility()));

                $traitMethods[] = $virtualMethod;
            }
            $methods = $methods->merge(VirtualReflectionMethodCollection::fromReflectionMethods($traitMethods));
        }

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

    public function interfaces(): CoreReflectionInterfaceCollection
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
     * @return CoreReflectionTraitCollection<ReflectionTrait>
     */
    public function traits(): CoreReflectionTraitCollection
    {
        $parentTraits = null;

        if ($this->parent()) {
            $parentTraits = $this->parent()->traits();
        }

        $traits = ReflectionTraitCollection::fromClassDeclaration($this->serviceLocator, $this->node);

        if ($parentTraits) {
            return $parentTraits->merge($traits);
        }

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
        return $this->serviceLocator->docblockFactory()->create($this->node()->getLeadingCommentAndWhitespaceText());
    }

    /**
     * @return ReflectionClassCollection<ReflectionClass>
     */
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

        $this->ancestors = TolerantReflectionClassCollection::fromReflections($this->serviceLocator, $ancestors);
        return $this->ancestors;
    }

    public function isFinal(): bool
    {
        $modifier = $this->node->abstractOrFinalModifier;

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
