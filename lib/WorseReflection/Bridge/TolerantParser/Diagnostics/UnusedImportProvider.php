<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\NamespaceUseClause;
use Microsoft\PhpParser\Node\NamespaceUseGroupClause;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\NamespaceDefinition;
use Microsoft\PhpParser\Token;
use Phpactor\DocblockParser\Ast\Docblock;
use Phpactor\DocblockParser\Ast\Type\CallableNode;
use Phpactor\DocblockParser\Ast\Type\ClassNode;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser\ParsedDocblock;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;

class UnusedImportProvider implements DiagnosticProvider
{
    /**
     * @var array<string,bool>
     */
    private array $usedPrefixes = [];

    /**
     * @var array<string,Node|Token>
     */
    private array $imported = [];

    public function enter(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        $docblock = $resolver->docblockFactory()->create(
            $node->getLeadingCommentAndWhitespaceText(),
            new ReflectionScope($resolver->reflector(), $node)
        );

        if ($docblock instanceof ParsedDocblock) {
            $this->extractDocblockNames($docblock->rawNode(), $resolver, $node);
        }

        if ($node instanceof QualifiedName && !$node->parent instanceof NamespaceUseClause && !$node->parent instanceof NamespaceDefinition && !$node->parent instanceof NamespaceUseGroupClause) {
            $prefix = $node->getNameParts()[0];
            if (!$prefix instanceof Token) {
                return [];
            }
            $usedPrefix = $this->prefixedName($node, (string)$prefix->getText($node->getFileContents()));
            $this->usedPrefixes[$usedPrefix] = true;
            return [];
        }

        if ($node instanceof NamespaceUseClause) {
            if ($node->groupClauses) {
                foreach ($node->groupClauses->children as $groupClause) {
                    if (!$groupClause instanceof NamespaceUseGroupClause) {
                        continue;
                    }
                    $useClause = $groupClause->parent->parent;
                    if (!$useClause instanceof NamespaceUseClause) {
                        continue;
                    }

                    $this->imported[$this->prefixedName($groupClause, $groupClause->__toString())] = $groupClause;
                }
                return [];
            }

            $prefix = (function (Node $clause): string {
                /** @phpstan-ignore-next-line TP lies */
                if ($clause->namespaceAliasingClause) {
                    return $this->prefixedName($clause, (string)$clause->namespaceAliasingClause->name->getText($clause->getFileContents()));
                }
                /** @phpstan-ignore-next-line TP lies */
                $lastPart = $this->lastPart((string)$clause->namespaceName);
                return $this->prefixedName($clause, $lastPart);
            })($node);

            $this->imported[$prefix] = $node;
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

        foreach ($this->imported as $importedName => $imported) {
            if (isset($this->usedPrefixes[$importedName])) {
                continue;
            }

            if ($this->usedByAnnotation($contents, $importedName, $imported)) {
                continue;
            }

            yield UnusedImportDiagnostic::for(
                ByteOffsetRange::fromInts($imported->getStartPosition(), $imported->getEndPosition()),
                explode(':', $importedName)[1]
            );
        }

        $this->imported = [];
        $this->usedPrefixes = [];

        return [];
    }

    public function examples(): iterable
    {
        return [];
    }

    private function extractDocblockNames(Docblock $docblock, NodeContextResolver $resolver, Node $node): void
    {
        $prefix = sprintf('%s:', $this->getNamespaceName($node));
        foreach ($docblock->descendantElements(ClassNode::class) as $type) {
            $this->usedPrefixes[$prefix . $type->toString()] = true;
        }
        foreach ($docblock->descendantElements(CallableNode::class) as $type) {
            assert($type instanceof CallableNode);
            if ($type->name->toString() === 'Closure') {
                $this->usedPrefixes[$prefix . 'Closure'] = true;
            }
        }
    }

    /**
     * @param Node|Token $node
     */
    private function usedByAnnotation(string $contents, string $imported, $node): bool
    {
        $imported = explode(':', $imported)[1];
        return str_contains($contents, '@' . $imported);
    }

    /** @phpstan-ignore-next-line TP lies */
    private function lastPart(string $name): string
    {
        $parts = array_filter(explode('\\', $name));
        if (!$parts) {
            return '';
        }
        return $parts[array_key_last($parts)];
    }

    private function prefixedName(Node $node, string $name): string
    {
        return sprintf('%s:%s', $this->getNamespaceName($node), $name);
    }

    private function getNamespaceName(Node $node): string
    {
        $definition = $node->getNamespaceDefinition();
        if (null === $definition) {
            return '';
        }
        if (!$definition->name instanceof QualifiedName) {
            return '';
        }
        return (string)$definition->name;
    }
}
