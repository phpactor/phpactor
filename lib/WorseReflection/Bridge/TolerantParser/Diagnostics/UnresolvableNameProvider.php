<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Attribute;
use Microsoft\PhpParser\Node\ClassBaseClause;
use Microsoft\PhpParser\Node\DelimitedList\QualifiedNameList;
use Microsoft\PhpParser\Node\Expression\BinaryExpression;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\Statement\FunctionDeclaration;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\ResolvedName;
use Microsoft\PhpParser\TokenKind;
use Phpactor\Name\FullyQualifiedName as PhpactorFullyQualifiedName;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Bridge\TolerantParser\Patch\TolerantQualifiedNameResolver;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\Reflector\FunctionReflector;
use Phpactor\TextDocument\TextDocument;

class UnresolvableNameProvider implements DiagnosticProvider
{
    public function __construct(private bool $importGlobals)
    {
    }

    public function exit(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        if (!$node instanceof QualifiedName) {
            return;
        }

        $name = $node;

        // Tolerant parser does not resolve names for constructs that define symbol names or aliases
        $resolvedName = TolerantQualifiedNameResolver::getResolvedName($name);

        // strange getResolvedName method returns a string if this is a
        // reserved name (e.g. static, iterable). do not return these as
        // "unresolved"
        if ($resolvedName && !$resolvedName instanceof ResolvedName) {
            return;
        }

        // Parser returns "NULL" for unqualified namespaced function / constant
        // names, but will return the FQN for references...
        if (!$resolvedName && $name->parent instanceof CallExpression) {
            yield from $this->forFunction(
                $resolver->reflector(),
                $name->getNamespacedName()->__toString(),
                $name,
            );
            return;
        }

        // if the tolerant parser did not provide the resolved name (because of
        // bug) then use the namespaced name.
        if (!$resolvedName) {
            $resolvedName = $name->getNamespacedName();
        }

        if (count($resolvedName->getNameParts()) == 0) {
            return;
        }

        // Function names in global namespace have a "resolved name"
        // (inconsistent parser behavior)
        if ($name->parent instanceof CallExpression) {
            yield from $this->forFunction(
                $resolver->reflector(),
                $name->getResolvedName() ?? $name->getText(),
                $name,
            );
            return;
        }

        $parent = $name->parent;
        if (
            !$parent instanceof ClassBaseClause &&
            !$parent instanceof QualifiedNameList &&
            !$parent instanceof ObjectCreationExpression &&
            !$parent instanceof ScopedPropertyAccessExpression &&
            !$parent instanceof FunctionDeclaration &&
            !$parent instanceof MethodDeclaration &&
            !$parent instanceof Attribute &&
            !($parent instanceof BinaryExpression && $parent->operator->kind === TokenKind::InstanceOfKeyword)
        ) {
            return;
        }

        yield from $this->forClass(
            $resolver->reflector(),
            $resolvedName,
            $name,
        );
    }

    public function enter(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        return [];
    }

    public function examples(): iterable
    {
        return [];
    }

    /**
     * @return iterable<UnresolvableNameDiagnostic>
     */
    private function forFunction(FunctionReflector $reflector, string $fqn, QualifiedName $name): iterable
    {
        $fqn = PhpactorFullyQualifiedName::fromString($fqn);

        try {
            // see comment for appendUnresolvedClassName
            $source = $reflector->sourceCodeForFunction($fqn->__toString());
            if (!$this->nameContainedInSource('function', $source, $fqn->head()->__toString())) {
                throw new NotFound();
            }
        } catch (NotFound) {
            // if we are not importing globals then check the global namespace
            if (false === $this->importGlobals) {
                try {
                    $source = $reflector->sourceCodeForFunction($fqn->head()->__toString());
                    if ($this->nameContainedInSource('function', $source, $fqn->head()->__toString())) {
                        return;
                    }
                } catch (NotFound) {
                }
            }

            yield UnresolvableNameDiagnostic::forFunction(
                ByteOffsetRange::fromInts($name->getStartPosition(), $name->getEndPosition()),
                $fqn,
            );
        }
    }

    private function nameContainedInSource(string $declarationPattern, TextDocument $source, string $nameText): bool
    {
        $lastPart = explode('\\', $nameText);
        $last = $lastPart[array_key_last($lastPart)];

        if ($source->__toString() === '') {
            return false;
        }

        return (bool)preg_match(sprintf('{%s\s+%s}', $declarationPattern, $last), $source->__toString());
    }

    /**
     * @return iterable<UnresolvableNameDiagnostic>
     */
    private function forClass(ClassReflector $reflector, string $fqn, QualifiedName $name): iterable
    {
        $fqn = PhpactorFullyQualifiedName::fromString($fqn);

        try {
            // we could reflect the class here but it's much more expensive
            // than simply locating the source, however locating the source
            // does not _guarantee_ that the name exists, so we additionally
            // ensure that at least the short name of the FQN is located in
            // the source code.
            $source = $reflector->sourceCodeForClassLike($fqn->__toString());
            if (!$this->nameContainedInSource('(class|trait|interface|enum)', $source, $fqn->head()->__toString())) {
                throw new NotFound();
            }
        } catch (NotFound) {
            yield UnresolvableNameDiagnostic::forClass(
                ByteOffsetRange::fromInts($name->getStartPosition(), $name->getEndPosition()),
                $fqn,
            );
        }
    }
}
