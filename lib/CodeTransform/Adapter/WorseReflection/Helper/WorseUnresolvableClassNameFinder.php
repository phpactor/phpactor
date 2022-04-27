<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Helper;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Attribute;
use Microsoft\PhpParser\Node\ClassBaseClause;
use Microsoft\PhpParser\Node\DelimitedList\QualifiedNameList;
use Microsoft\PhpParser\Node\Expression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Parameter;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\Statement\FunctionDeclaration;
use Microsoft\PhpParser\ResolvedName;
use Phpactor\CodeTransform\Domain\NameWithByteOffset;
use Phpactor\CodeTransform\Domain\NameWithByteOffsets;
use Phpactor\Name\QualifiedName as PhpactorQualifiedName;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeTransform\Domain\Helper\UnresolvableClassNameFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Bridge\TolerantParser\Patch\TolerantQualifiedNameResolver;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Reflector;

class WorseUnresolvableClassNameFinder implements UnresolvableClassNameFinder
{
    private Parser $parser;

    private Reflector $reflector;

    public function __construct(Reflector $reflector, Parser $parser = null)
    {
        $this->parser = $parser ?: new Parser();
        $this->reflector = $reflector;
    }

    public function find(TextDocument $sourceCode): NameWithByteOffsets
    {
        $rootNode = $this->parser->parseSourceFile($sourceCode);
        $names = $this->findNameNodes($rootNode);
        $names = $this->filterResolvedNames($names);

        return new NameWithByteOffsets(...$names);
    }

    private function findNameNodes(SourceFileNode $rootNode): array
    {
        $names = [];
        foreach ($this->descendants($rootNode) as $node) {
            if (!$node instanceof QualifiedName) {
                continue;
            }

            if (isset($names[(string)$node])) {
                continue;
            }

            $names[(string)$node] = $node;
        }

        return array_values($names);
    }

    private function filterResolvedNames(array $names): array
    {
        $unresolvedNames = [];
        $notFound = [];
        foreach ($names as $name) {
            $unresolvedNames = $this->appendUnresolvedName($name, $unresolvedNames, $notFound);
        }

        return $unresolvedNames;
    }

    private function appendUnresolvedName(QualifiedName $name, array $unresolvedNames, array &$notFoundCache): array
    {
        // Tolerant parser does not resolve names for constructs that define symbol names or aliases
        $resolvedName = TolerantQualifiedNameResolver::getResolvedName($name);

        // Parser returns "NULL" for unqualified namespaced function / constant
        // names, but will return the FQN for references...
        if (!$resolvedName && $name->parent instanceof CallExpression) {
            return $this->appendUnresolvedFunctionName(
                $name->getNamespacedName()->__toString(),
                $unresolvedNames,
                $name,
                $notFoundCache
            );
        }

        // strange getResolvedName method returns a string if this is a
        // reserved name (e.g. static, iterable). do not return these as
        // "unresolved"
        if ($resolvedName && !$resolvedName instanceof ResolvedName) {
            return $unresolvedNames;
        }

        // if the tolerant parser did not provide the resolved name (because of
        // bug) then use the namespaced name.
        if (!$resolvedName) {
            $resolvedName = $name->getNamespacedName();
        }

        if (count($resolvedName->getNameParts()) == 0) {
            return $unresolvedNames;
        }

        // Function names in global namespace have a "resolved name"
        // (inconsistent parser behavior)
        if ($name->parent instanceof CallExpression) {
            return $this->appendUnresolvedFunctionName(
                $name->getResolvedName() ?? $name->getText(),
                $unresolvedNames,
                $name,
                $notFoundCache
            );
        }

        if (
            !$name->parent instanceof ClassBaseClause &&
            !$name->parent instanceof QualifiedNameList &&
            !$name->parent instanceof ObjectCreationExpression &&
            !$name->parent instanceof ScopedPropertyAccessExpression &&
            !$name->parent instanceof FunctionDeclaration &&
            !$name->parent instanceof MethodDeclaration &&
            !$name->parent instanceof Expression &&
            !$name->parent instanceof Parameter &&
            !$name->parent instanceof Attribute
        ) {
            return $unresolvedNames;
        }

        return $this->appendUnresolvedClassName(
            $resolvedName,
            $unresolvedNames,
            $name,
            $notFoundCache
        );
    }

    /**
     * @param array<string,NotFound> $notFoundCache
     */
    private function appendUnresolvedClassName(string $nameText, array $unresolvedNames, QualifiedName $name, &$notFoundCache): array
    {
        $cacheKey =  'c:' . $nameText;
        try {
            if (isset($notFoundCache[$cacheKey])) {
                throw $notFoundCache[$cacheKey];
            }

            // we could reflect the class here but it's much more expensive
            // than simply locating the source, however locating the source
            // does not _guarantee_ that the name exists, so we additionally
            // ensure that at least the short name of the FQN is located in
            // the source code.
            $source = $this->reflector->sourceCodeForClassLike($nameText);
            if (!$this->nameContainedInSource('(class|trait|interface|enum)', $source, $nameText)) {
                throw new NotFound();
            }
        } catch (NotFound $notFound) {
            $notFoundCache[$cacheKey] = $notFound;
            $unresolvedNames[] = new NameWithByteOffset(
                PhpactorQualifiedName::fromString($nameText),
                ByteOffset::fromInt($name->getStartPosition()),
                NameWithByteOffset::TYPE_CLASS
            );
        }
        
        return $unresolvedNames;
    }

    /**
     * @param array<string,NotFound> $notFoundCache
     */
    private function appendUnresolvedFunctionName(string $nameText, array $unresolvedNames, QualifiedName $name, &$notFoundCache): array
    {
        $cacheKey = 'f:' . $nameText;
        try {
            if (isset($notFoundCache[$cacheKey])) {
                throw $notFoundCache[$cacheKey];
            }

            // see comment for appendUnresolvedClassName
            $source = $this->reflector->sourceCodeForFunction($nameText);
            if (!$this->nameContainedInSource('function', $source, $nameText)) {
                throw new NotFound();
            }
        } catch (NotFound $notFound) {
            $notFoundCache[$cacheKey] = $notFound;
            $unresolvedNames[] = new NameWithByteOffset(
                PhpactorQualifiedName::fromString($nameText),
                ByteOffset::fromInt($name->getStartPosition()),
                NameWithByteOffset::TYPE_FUNCTION
            );
        }
        
        return $unresolvedNames;
    }

    private function descendants(Node $node): array
    {
        $descendants = [];
        foreach ($node->getChildNodes() as $childNode) {
            if (!$childNode instanceof Node) {
                continue;
            }

            $descendants[] = $childNode;
            $descendants = array_merge($descendants, $this->descendants($childNode));
        }

        return $descendants;
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
