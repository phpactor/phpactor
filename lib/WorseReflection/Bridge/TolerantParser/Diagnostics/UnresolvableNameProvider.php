<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Bridge\TolerantParser\Patch\TolerantQualifiedNameResolver;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Reflector\FunctionReflector;
use Phpactor\WorseReflection\Core\SourceCode;
use Reflector;

class UnresolvableNameProvider implements DiagnosticProvider
{
    public function provide(NodeContextResolver $resolver, Frame $frame, Node $node): Generator
    {
        if (!$node instanceof QualifiedName) {
            return;
        }

        $name = $node;

        // Tolerant parser does not resolve names for constructs that define symbol names or aliases
        $resolvedName = TolerantQualifiedNameResolver::getResolvedName($name);

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

        yield UnresolvableNameDiagnostic::forFunction(
            ByteOffsetRange::fromInts($node->getStartPosition(), $node->getEndPosition()),
            $name->__toString(),
        );
    }

    /**
     * @return Generator<UnresolvableNameDiagnostic>
     */
    private function forFunction(FunctionReflector $reflector, string $fqn, QualifiedName $name): Generator
    {
        try {
            // see comment for appendUnresolvedClassName
            $source = $reflector->sourceCodeForFunction($fqn);
            if (!$this->nameContainedInSource('function', $source, $fqn)) {
                throw new NotFound();
            }
        } catch (NotFound $notFound) {
            yield new UnresolvableNameDiagnostic(
                ByteOffsetRange::fromInts($name->getStartPosition(), $name->getEndPosition())
            );
        }
    }

    private function nameContainedInSource(string $declarationPattern, SourceCode $source, string $nameText): bool
    {
        $lastPart = explode('\\', $nameText);
        $last = $lastPart[array_key_last($lastPart)];

        if ($source->__toString() === '') {
            return false;
        }

        return (bool)preg_match(sprintf('{%s\s+%s}', $declarationPattern, $last), $source->__toString());
    }
}
