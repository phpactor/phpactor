<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node\Parameter;
use Microsoft\PhpParser\TokenKind;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TypeResolver\DeclaredMemberTypeResolver;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
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
use Phpactor\WorseReflection\Core\Visibility;
use InvalidArgumentException;

class ReflectionPromotedProperty extends AbstractReflectionClassMember implements CoreReflectionProperty
{
    private ServiceLocator $serviceLocator;
    
    private ReflectionClassLike $class;
    
    private PropertyTypeResolver $typeResolver;
    
    private DeclaredMemberTypeResolver $memberTypeResolver;
    
    private Parameter $parameter;

    public function __construct(
        ServiceLocator $serviceLocator,
        ReflectionClass $class,
        Parameter $parameter
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->class = $class;
        $this->typeResolver = new PropertyTypeResolver($this, $this->serviceLocator->logger());
        $this->memberTypeResolver = new DeclaredMemberTypeResolver($this->serviceLocator->reflector());
        $this->parameter = $parameter;
    }

    public function declaringClass(): ReflectionClassLike
    {
        /** @var NamespacedNameInterface $classDeclaration */
        $classDeclaration = $this->parameter->getFirstAncestor(ClassDeclaration::class, TraitDeclaration::class);
        $class = $classDeclaration->getNamespacedName();

        /** @phpstan-ignore-next-line */
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
        return (string) $this->parameter->getName();
    }

    public function inferredTypes(): Types
    {
        $types = $this->typeResolver->resolve();

        if ($this->parameter->typeDeclarationList) {
            $types = $this->memberTypeResolver->resolveTypes(
                $this->parameter,
                $this->parameter->typeDeclarationList,
                $this->class()->name(),
                $this->parameter->questionToken ? true : false
            );
        }

        return $types;
    }

    public function type(): Type
    {
        return $this->memberTypeResolver->resolve(
            $this->parameter,
            $this->parameter->typeDeclarationList,
            $this->class()->name(),
            $this->parameter->questionToken ? true : false
        );
    }

    public function class(): ReflectionClassLike
    {
        return $this->class;
    }

    public function isStatic(): bool
    {
        return false;
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
        return true;
    }

    public function visibility(): Visibility
    {
        $node = $this->parameter;

        if (!$node->visibilityToken) {
            return Visibility::public();
        }

        if ($node->visibilityToken->kind === TokenKind::PrivateKeyword) {
            return Visibility::private();
        }

        if ($node->visibilityToken->kind === TokenKind::ProtectedKeyword) {
            return Visibility::protected();
        }

        return Visibility::public();
    }

    protected function node(): Node
    {
        return $this->parameter;
    }

    protected function serviceLocator(): ServiceLocator
    {
        return $this->serviceLocator;
    }
}
