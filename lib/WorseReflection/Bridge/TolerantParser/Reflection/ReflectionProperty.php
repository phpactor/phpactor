<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TypeResolver\DeclaredMemberTypeResolver;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty as CoreReflectionProperty;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\TypeResolver\PropertyTypeResolver;
use Phpactor\WorseReflection\Core\Types;
use Microsoft\PhpParser\NamespacedNameInterface;
use Phpactor\WorseReflection\Core\Type;
use InvalidArgumentException;

class ReflectionProperty extends AbstractReflectionClassMember implements CoreReflectionProperty
{
    private ServiceLocator $serviceLocator;
    
    private PropertyDeclaration $propertyDeclaration;
    
    private Variable $variable;
    
    private ReflectionClassLike $class;
    
    private PropertyTypeResolver $typeResolver;
    
    private DeclaredMemberTypeResolver $memberTypeResolver;

    public function __construct(
        ServiceLocator $serviceLocator,
        ReflectionClassLike $class,
        PropertyDeclaration $propertyDeclaration,
        Variable $variable
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->propertyDeclaration = $propertyDeclaration;
        $this->variable = $variable;
        $this->class = $class;
        $this->typeResolver = new PropertyTypeResolver($this, $this->serviceLocator->logger());
        $this->memberTypeResolver = new DeclaredMemberTypeResolver($this->serviceLocator->reflector());
    }

    public function declaringClass(): ReflectionClassLike
    {
        /** @var NamespacedNameInterface $classDeclaration */
        $classDeclaration = $this->propertyDeclaration->getFirstAncestor(ClassDeclaration::class, TraitDeclaration::class);
        $class = $classDeclaration->getNamespacedName();

        if (null === $class) {
            throw new InvalidArgumentException(sprintf(
                'Could not locate class-like ancestor node for method "%s"',
                $this->name()
            ));
        }

        return $this->serviceLocator->reflector()->reflectClassLike(ClassName::fromString($class));
    }

    public function name(): string
    {
        return (string) $this->variable->getName();
    }

    public function inferredTypes(): Types
    {
        $types = $this->typeResolver->resolve();

        $types = $types->merge($this->memberTypeResolver->resolveTypes(
            $this->propertyDeclaration,
            $this->propertyDeclaration->typeDeclarationList,
            $this->class()->name(),
            $this->propertyDeclaration->questionToken ? true : false
        ));

        return $types;
    }

    public function type(): Type
    {
        return $this->memberTypeResolver->resolve(
            $this->propertyDeclaration,
            $this->propertyDeclaration->typeDeclarationList,
            $this->class()->name(),
            $this->propertyDeclaration->questionToken ? true : false
        );
    }

    public function class(): ReflectionClassLike
    {
        return $this->class;
    }

    public function isStatic(): bool
    {
        return $this->propertyDeclaration->isStatic();
    }

    public function isVirtual(): bool
    {
        return false;
    }

    public function memberType(): string
    {
        return ReflectionMember::TYPE_PROPERTY;
    }

    public function isPromoted(): bool
    {
        return false;
    }

    protected function node(): Node
    {
        return $this->propertyDeclaration;
    }

    protected function serviceLocator(): ServiceLocator
    {
        return $this->serviceLocator;
    }
}
