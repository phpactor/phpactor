<?php

namespace Phpactor\ClassMover\Domain\Reference;

use Phpactor\ClassMover\Domain\Name\FullyQualifiedName;
use IteratorAggregate;
use ArrayIterator;

final class NamespacedClassReferences implements IteratorAggregate
{
    private $classRefs = [];
    private $namespaceRef;

    private function __construct(NamespaceReference $namespaceRef, array $classRefs)
    {
        $this->namespaceRef = $namespaceRef;
        foreach ($classRefs as $classRef) {
            $this->add($classRef);
        }
    }

    public static function fromNamespaceAndClassRefs(NamespaceReference $namespace, array $classRefs): NamespacedClassReferences
    {
        return new self($namespace, $classRefs);
    }

    public static function empty()
    {
        return new self(NamespaceReference::forRoot(), []);
    }

    public function filterForName(FullyQualifiedName $name): NamespacedClassReferences
    {
        return new self($this->namespaceRef, array_filter($this->classRefs, function (ClassReference $classRef) use ($name) {
            return $classRef->fullName()->isEqualTo($name);
        }));
    }

    public function isEmpty(): bool
    {
        return empty($this->classRefs);
    }

    public function getIterator()
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
