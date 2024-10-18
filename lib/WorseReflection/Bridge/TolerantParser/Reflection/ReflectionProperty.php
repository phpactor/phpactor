<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Phpactor\TextDocument\ByteOffsetRange;
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
use Microsoft\PhpParser\NamespacedNameInterface;
use Phpactor\WorseReflection\Core\Type;
use InvalidArgumentException;

class ReflectionProperty extends AbstractReflectionClassMember implements CoreReflectionProperty
{
    private PropertyTypeResolver $typeResolver;

    private DeclaredMemberTypeResolver $memberTypeResolver;

    private ?string $name = null;

    public function __construct(
        private ServiceLocator $serviceLocator,
        private ReflectionClassLike $class,
        private PropertyDeclaration $propertyDeclaration,
        private Variable $variable
    ) {
        $this->typeResolver = new PropertyTypeResolver($this);
        $this->memberTypeResolver = new DeclaredMemberTypeResolver($this->serviceLocator->reflector());
    }

    public function declaringClass(): ReflectionClassLike
    {
        /** @var NamespacedNameInterface|null $classDeclaration */
        $classDeclaration = $this->propertyDeclaration->getFirstAncestor(ClassDeclaration::class, TraitDeclaration::class);
        $class = $classDeclaration?->getNamespacedName();

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
        if ($this->name) {
            return $this->name;
        }
        $this->name = (string) $this->variable->getName();
        return $this->name;
    }

    public function nameRange(): ByteOffsetRange
    {
        return ByteOffsetRange::fromInts(
            $this->variable->getStartPosition() + 1, // do not return the $
            $this->variable->getEndPosition(),
        );
    }

    public function inferredType(): Type
    {
        $type = $this->typeResolver->resolve();

        if (($type->isDefined())) {
            return $type;
        }

        return $this->memberTypeResolver->resolveTypes(
            $this->propertyDeclaration,
            $this->propertyDeclaration->typeDeclarationList,
            $this->class()->name(),
            $this->propertyDeclaration->questionToken ? true : false
        );
    }

    public function type(): Type
    {
        return $this->memberTypeResolver->resolveTypes(
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

    public function withClass(ReflectionClassLike $class): ReflectionMember
    {
        return new self($this->serviceLocator, $class, $this->propertyDeclaration, $this->variable);
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
