<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\NamespaceUseClause;
use Microsoft\PhpParser\Node\NamespaceUseGroupClause;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\NamespaceDefinition;
use Microsoft\PhpParser\Token;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser\ParsedDocblock;
use Phpactor\WorseReflection\Bridge\TolerantParser\Patch\TolerantQualifiedNameResolver;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\TypeResolver\DefaultTypeResolver;
use Phpactor\WorseReflection\Core\Type\ClassType;

class UnusedImportProvider implements DiagnosticProvider
{
    /**
     * @var array<string,bool>
     */
    private array $names = [];

    /**
     * @var array<string,Node|Token>
     */
    private array $imported = [];

    public function enter(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        $docblock = $resolver->docblockFactory()->create($node->getLeadingCommentAndWhitespaceText());

        if ($docblock instanceof ParsedDocblock) {
            $this->extractDocblockNames($docblock, $resolver, $node);
        }

        if ($node instanceof QualifiedName && !$node->parent instanceof NamespaceUseClause && !$node->parent instanceof NamespaceDefinition) {
            $resolvedName = (new TolerantQualifiedNameResolver())->getResolvedName($node);
            if (null === $resolvedName) {
                return [];
            }
            $this->names[(string)$resolvedName] = true;
            return [];
        }

        if ($node instanceof NamespaceUseClause) {
            if ($node->groupClauses) {
                foreach ($node->groupClauses->children as $groupClause) {
                    if (!$groupClause instanceof NamespaceUseGroupClause) {
                        continue;
                    }
                    $parent = $groupClause->parent->parent;
                    if (!$parent instanceof NamespaceUseClause) {
                        continue;
                    }

                    $parent = $parent->namespaceName->__toString();
                    $this->imported[$parent . $groupClause->namespaceName->getNamespacedName()->__toString()] = $groupClause;
                }
                return [];
            }

            $this->imported[$node->namespaceName->__toString()] = $node;
            return [];
        }

        return [];
    }

    public function exit(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        if (!$node instanceof SourceFileNode) {
            return [];
        }

        $contents = $node->getFileContents();

        foreach ($this->imported as $importedFqn => $imported) {
            if (isset($this->names[$importedFqn])) {
                continue;
            }

            if ($this->usedByAnnotation($contents, $importedFqn, $imported)) {
                continue;
            }
                
            yield UnusedImportDiagnostic::for(
                ByteOffsetRange::fromInts($imported->getStartPosition(), $imported->getEndPosition()),
                $importedFqn
            );
        }

        $this->imported = [];
        $this->names = [];

        return [];
    }

    private function extractDocblockNames(ParsedDocblock $docblock, NodeContextResolver $resolver, Node $node): void
    {
        // horrbile hack to get the fully qualified class name for the docblock
        $docblock = $docblock->withTypeResolver(
            new DefaultTypeResolver(
                new ReflectionScope($resolver->reflector(), $node)
            )
        );
        assert($docblock instanceof ParsedDocblock);
        foreach ($docblock->types() as $type) {
            if ($type instanceof ClassType) {
                $this->names[$type->name()->full()] = true;
            }
        }
    }

    /**
     * @param Node|Token $node
     */
    private function usedByAnnotation(string $contents, string $imported, $node): bool
    {
        $name = (function () use ($imported, $node, $contents) {
            /** @phpstan-ignore-next-line TP lies */
            if ($node instanceof NamespaceUseClause && $node->namespaceAliasingClause) {
                return $node->namespaceAliasingClause->name->getText($contents);
            }

            $imported = explode('\\', $imported);
            return array_pop($imported);
        })();

        return false !== strpos($contents, '@' . $name);
    }
}
