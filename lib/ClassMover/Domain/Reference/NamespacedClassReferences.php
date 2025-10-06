<?php

namespace Phpactor\ClassMover\Domain\Reference;

use Phpactor\ClassMover\Domain\Name\FullyQualifiedName;
use IteratorAggregate;
use ArrayIterator;
use Traversable;

/**
 * @implements IteratorAggregate<ClassReference>
 */
final class NamespacedClassReferences implements IteratorAggregate
{
    /**
     * @var ClassReference[]
     */
    private array $classRefs = [];

    /**
     * @param ClassReference[] $classRefs
     */
    private function __construct(private NamespaceReference $namespaceRef, array $classRefs)
    {
        foreach ($classRefs as $classRef) {
            $this->add($classRef);
        }
    }

    /**
     * @param ClassReference[] $classRefs
     */
    public static function fromNamespaceAndClassRefs(NamespaceReference $namespace, array $classRefs): NamespacedClassReferences
    {
        return new self($namespace, $classRefs);
    }

    public static function empty(): self
    {
        return new self(NamespaceReference::forRoot(), []);
    }

    public function filterForName(FullyQualifiedName $name): NamespacedClassReferences
    {
        return new self($this->namespaceRef, array_filter($this->classRefs, function (ClassReference $classRef) use ($name) {
            return $classRef->fullName()->isEqualTo($name);
        }));
    }

    public function filterLocal(FullyQualifiedName $name): NamespacedClassReferences
    {
        return new self($this->namespaceRef, array_filter($this->classRefs, function (ClassReference $classRef) use ($name) {
            return $classRef->fullName()->parentNamespace()->equals($name->parentNamespace())
                && false === $classRef->fullName()->equals($name); // skip self reference
        }));
    }

    public function isEmpty(): bool
    {
        return $this->classRefs === [];
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->classRefs);
    }

    public function namespaceRef(): NamespaceReference
    {
        return $this->namespaceRef;
    }

    private function add(ClassReference $classRef): void
    {
        $this->classRefs[] = $classRef;
    }
}
