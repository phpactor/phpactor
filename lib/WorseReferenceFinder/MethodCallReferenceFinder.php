<?php

namespace Phpactor\WorseReferenceFinder;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\Statement\FunctionDeclaration;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Token;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\ReferenceFinder\PotentialLocation;
use Phpactor\ReferenceFinder\ReferenceFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\AstProvider;

/**
 * Finds references to a method or function by searching all open documents
 * in the workspace for matching call expressions.
 *
 * Unlike TolerantVariableReferenceFinder (which only finds variable
 * references within a single document), this finder searches across all
 * open documents to find method call sites.
 */
class MethodCallReferenceFinder implements ReferenceFinder
{
    public function __construct(
        private AstProvider $astProvider,
        private Workspace $workspace,
    ) {
    }

    /**
     * @return Generator<PotentialLocation>
     */
    public function findReferences(TextDocument $document, ByteOffset $byteOffset): Generator
    {
        $methodName = $this->resolveMethodName($document, $byteOffset->toInt());
        if ($methodName === null) {
            return;
        }

        foreach ($this->workspace as $uri => $lspDoc) {
            $doc = \Phpactor\Extension\LanguageServerBridge\Converter\TextDocumentConverter::fromLspTextItem($lspDoc);
            $sourceNode = $this->astProvider->get($doc);
            yield from $this->walkForCalls($sourceNode, $methodName, (string)$doc->uriOrThrow());
        }
    }

    private function resolveMethodName(TextDocument $document, int $offset): ?string
    {
        $sourceNode = $this->astProvider->get($document);
        $node = $sourceNode->getDescendantNodeAtPosition($offset);

        // Walk up to find the enclosing MethodDeclaration or FunctionDeclaration
        $current = $node;
        while ($current !== null) {
            if ($current instanceof MethodDeclaration || $current instanceof FunctionDeclaration) {
                $nameToken = $current->name;
                if ($nameToken instanceof Token) {
                    return (string)$nameToken->getText($current->getFileContents());
                }
                return null;
            }
            $current = $current->parent;
        }

        // If the node is a MemberAccessExpression, get the member name
        if ($node instanceof MemberAccessExpression) {
            return (string)$node->memberName->getText($node->getFileContents());
        }

        // If the node is a ScopedPropertyAccessExpression, get the member name
        if ($node instanceof ScopedPropertyAccessExpression) {
            return (string)$node->memberName->getText($node->getFileContents());
        }

        return null;
    }

    /**
     * Recursively walks the AST, yielding a PotentialLocation for each
     * call site whose callable name matches $methodName.
     *
     * @return Generator<PotentialLocation>
     */
    private function walkForCalls(Node $node, string $methodName, string $uri): Generator
    {
        if ($node instanceof CallExpression) {
            $callable = $node->callableExpression;

            if ($callable instanceof MemberAccessExpression
                && $callable->memberName->getText($callable->getFileContents()) === $methodName) {
                yield PotentialLocation::surely(
                    Location::fromPathAndOffsets($uri, $node->getStartPosition(), $node->getEndPosition())
                );
            }

            if ($callable instanceof ScopedPropertyAccessExpression
                && $callable->memberName->getText($callable->getFileContents()) === $methodName) {
                yield PotentialLocation::surely(
                    Location::fromPathAndOffsets($uri, $node->getStartPosition(), $node->getEndPosition())
                );
            }
        }

        foreach ($node->getChildNodes() as $child) {
            yield from $this->walkForCalls($child, $methodName, $uri);
        }
    }
}
