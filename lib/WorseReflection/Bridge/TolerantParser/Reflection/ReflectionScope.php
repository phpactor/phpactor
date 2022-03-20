<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\Reflection\ReflectionScope as CoreReflectionScope;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\NameImports;
use Phpactor\WorseReflection\Core\Name;
use Microsoft\PhpParser\ResolvedName;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Inference\FullyQualifiedNameResolver;
use Phpactor\WorseReflection\Bridge\PsrLog\ArrayLogger;
use Phpactor\WorseReflection\Reflector;

class ReflectionScope implements CoreReflectionScope
{
    private Node $node;

    private Reflector $reflector;

    public function __construct(Reflector $reflector, Node $node)
    {
        $this->node = $node;
        $this->reflector = $reflector;
    }

    /**
     * @return NameImports<Name>
     */
    public function nameImports(): NameImports
    {
        list($nameImports) = $this->node->getImportTablesForCurrentScope();
        return NameImports::fromNames(array_map(function (ResolvedName $name) {
            return Name::fromParts($name->getNameParts());
        }, $nameImports));
    }

    public function namespace(): Name
    {
        $namespaceDefinition = $this->node->getNamespaceDefinition();

        if (null === $namespaceDefinition) {
            return Name::fromString('');
        }

        if (null === $namespaceDefinition->name) {
            return Name::fromString('');
        }

        return Name::fromString($namespaceDefinition->name->getText());
    }

    public function resolveFullyQualifiedName($type, ReflectionClassLike $class = null): Type
    {
        $resolver = new FullyQualifiedNameResolver($this->reflector, new ArrayLogger());
        return $resolver->resolve($this->node, $type, $class ? $class->name() : null);
    }

    public function resolveLocalName(Name $name): Name
    {
        return $this->nameImports()->resolveLocalName($name);
    }
}
