<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Amp\CancellationToken;
use Amp\Promise;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\NamespaceUseClause;
use Microsoft\PhpParser\Node\NamespaceUseGroupClause;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\NamespaceDefinition;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;

class UnusedImportProvider implements DiagnosticProvider
{
    /**
     * @var array<string,bool>
     */
    private array $names = [];

    /**
     * @var array<string,NamespaceUseClause>
     */
    private array $imported = [];

    public function enter(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        if ($node instanceof QualifiedName && !$node->parent instanceof NamespaceUseClause && !$node->parent instanceof NamespaceDefinition) {
            $resolvedName = $node->getResolvedName();
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
                    $this->imported[$groupClause->namespaceName->__toString()] = $groupClause;
                }
                return [];
            }
            $this->imported[$node->__toString()] = $node;
            return [];
        }

        return [];
    }

    public function exit(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        if (!$node instanceof SourceFileNode) {
            return [];
        }

        foreach ($this->imported as $importedFqn => $imported) {
            if (isset($this->names[$importedFqn])) {
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

}
