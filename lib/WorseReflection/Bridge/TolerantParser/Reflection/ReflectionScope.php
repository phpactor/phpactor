<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope as CoreReflectionScope;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\NameImports;
use Phpactor\WorseReflection\Core\Name;
use Microsoft\PhpParser\ResolvedName;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Inference\NodeToTypeConverter;
use Phpactor\WorseReflection\Bridge\PsrLog\ArrayLogger;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\UnionType;
use Phpactor\WorseReflection\Reflector;

class ReflectionScope implements CoreReflectionScope
{
    public function __construct(
        private readonly Reflector $reflector,
        private readonly Node $node
    ) {
    }

    /**
     * @return NameImports<Name>
     */
    public function nameImports(): NameImports
    {
        [$nameImports] = $this->node->getImportTablesForCurrentScope();
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

        if (!$namespaceDefinition->name instanceof QualifiedName) {
            return Name::fromString('');
        }

        return Name::fromString($namespaceDefinition->name->getText());
    }

    public function resolveFullyQualifiedName($type, ?ReflectionClassLike $class = null): Type
    {
        $resolver = new NodeToTypeConverter($this->reflector, new ArrayLogger());
        return $resolver->resolve($this->node, $type, $class ? $class->name() : null);
    }

    public function resolveLocalName(Name $name): Name
    {
        return $this->nameImports()->resolveLocalName($name);
    }

    public function resolveLocalType(Type $type): Type
    {
        $union = UnionType::toUnion($type);
        foreach ($union->types as $type) {
            if ($type instanceof ClassType) {
                $type->name = ClassName::fromString($this->nameImports()->resolveLocalName($type->name())->__toString());
            }
        }
        return $union->reduce();
    }
}
