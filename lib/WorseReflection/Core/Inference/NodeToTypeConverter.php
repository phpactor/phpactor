<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Type;
use Microsoft\PhpParser\ClassLike;
use Microsoft\PhpParser\Node\NamespaceUseClause;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\NamespacedNameInterface;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\ScalarType;
use Phpactor\WorseReflection\Core\Type\SelfType;
use Phpactor\WorseReflection\Core\Type\StaticType;
use Phpactor\WorseReflection\Reflector;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * This class is responsible for adjusting types according to their context
 * (e.g. expanding to FQNs).
 *
 * This should not be done here but rather ealier when the docblock types are
 * initially converted.
 *
 * https://github.com/phpactor/phpactor/issues/1781
 */
class NodeToTypeConverter
{
    public function __construct(
        private readonly Reflector $reflector,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }

    /**
     * @param null|Type|string $type
     */
    public function resolve(Node $node, $type = null, ?Name $currentClass = null): Type
    {
        $type = $type ?: $node->getText();

        /** @var Type $type */
        $type = $type instanceof Type ? $type : TypeFactory::fromStringWithReflector($type, $this->reflector);

        if ($this->isUseDefinition($node)) {
            return TypeFactory::fromStringWithReflector((string) $type, $this->reflector);
        }

        if ($type instanceof ScalarType) {
            return $type;
        }

        if ($type instanceof ClassType && $type->name->wasFullyQualified()) {
            return $type;
        }

        if ($type instanceof SelfType || $type instanceof StaticType) {
            return $this->currentClass($node, $currentClass);
        }

        if ($type instanceof ClassType && (string) $type == 'parent') {
            return $this->parentClass($node);
        }

        if ($importedType = $this->fromClassImports($node, $type)) {
            return $importedType;
        }

        $namespaceDefinition = $node->getNamespaceDefinition();
        if ($type instanceof ClassType && $namespaceDefinition && $namespaceDefinition->name instanceof QualifiedName) {
            $className = $type->name->prepend($namespaceDefinition->name->getText());
            $type->name = $className;

            return $type;
        }

        return $type;
    }

    private function parentClass(Node $node): Type
    {
        /** @var ClassDeclaration $class */
        $class = $node->getFirstAncestor(ClassDeclaration::class);

        /** @phpstan-ignore-next-line */
        if (null === $class) {
            $this->logger->warning('"parent" keyword used outside of class scope');
            return TypeFactory::unknown();
        }

        if (null === $class->classBaseClause) {
            $this->logger->warning('"parent" keyword used but class does not extend anything');
            return TypeFactory::unknown();
        }


        return TypeFactory::fromStringWithReflector(
            $class->classBaseClause->baseClass->getResolvedName(),
            $this->reflector
        );
    }

    private function currentClass(Node $node, ?Name $currentClass = null): Type
    {
        if ($currentClass) {
            return TypeFactory::fromStringWithReflector($currentClass->full(), $this->reflector);
        }
        $class = $node->getFirstAncestor(ClassLike::class);

        if (null === $class) {
            return TypeFactory::unknown();
        }

        assert($class instanceof NamespacedNameInterface);

        return TypeFactory::fromStringWithReflector($class->getNamespacedName(), $this->reflector);
    }

    private function isUseDefinition(Node $node): bool
    {
        return $node->getParent() instanceof NamespaceUseClause;
    }

    private function fromClassImports(Node $node, Type $type): ?Type
    {
        $imports = $node->getImportTablesForCurrentScope();
        $classImports = $imports[0];

        if (!$type instanceof ClassType) {
            return $type;
        }

        $className = $type->name->__toString();

        if (isset($classImports[$className])) {
            $type->name = ClassName::fromString((string) $classImports[$className]);
            return $type;
        }

        if (isset($classImports[$type->name->head()->__toString()])) {
            $type->name = ClassName::fromString(
                (string) $classImports[(string) $type->name->head()] . '\\' . (string) $type->name->tail()
            );
            return $type;
        }

        return null;
    }
}
