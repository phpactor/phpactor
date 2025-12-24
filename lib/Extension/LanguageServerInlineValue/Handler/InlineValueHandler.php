<?php

namespace Phpactor\Extension\LanguageServerInlineValue\Handler;

use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\Parameter;
use Microsoft\PhpParser\Node\Expression\SubscriptExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Amp\Success;
use Amp\Promise;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Token;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServerProtocol\TextDocumentIdentifier;
use Phpactor\LanguageServerProtocol\InlineValueVariableLookup;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\TextDocument\TextDocumentBuilder;
use CallbackFilterIterator;

class InlineValueHandler implements Handler, CanRegisterCapabilities
{
    public function __construct(
        private Workspace $workspace,
        private Parser $parser,
    ) {
    }

    public function methods(): array
    {
        return [
            'textDocument/inlineValue' => 'inlineValue',
        ];
    }

    /**
     * @return Promise<InlineValueVariableLookup[]|null>
     */
    public function inlineValue(
        TextDocumentIdentifier $textDocument,
        Range $range
    ): Promise {
        $document = $this->workspace->get($textDocument->uri);
        $document = TextDocumentBuilder::create($document->text)
            ->uri($document->uri)
            ->language('php')
            ->build();

        $start = PositionConverter::positionToByteOffset($range->start, $document)->toInt();
        $end = PositionConverter::positionToByteOffset($range->end, $document)->toInt();

        $root = $this->parser->parseSourceFile((string) $document, $document->uri()?->__toString());

        $i = $root->getDescendantNodes(fn ($child) => $child->getStartPosition() <= $end && $child->getEndPosition() >= $start);
        $i = new CallbackFilterIterator($i, fn ($node) =>
            ($node instanceof Variable ||
            $node instanceof Parameter));
        $ret = array_map(
            function ($node) {
                /** @var Node $node */
                $ev = $this->nodeToEvaluatable($node);
                if ($ev === null) {
                    return null;
                }
                return new InlineValueVariableLookup(
                    range: $ev['range'],
                    caseSensitiveLookup: true,
                    variableName: $ev['expression']
                );
            },
            \iterator_to_array($i, false)
        );
        $ret = array_filter($ret);
        return new Success($ret);
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities): void
    {
        $capabilities->inlineValueProvider = true;
    }

    /**
     * @return array{expression:string,range:Range}|null
     */
    private function nodeToEvaluatable(Node $node): ?array
    {
        if ($node instanceof Parameter) {
            return $this->nodeToExpressionRange($node->variableName, $node);
        }
        if (
            $node instanceof Variable ||
            $node instanceof SubscriptExpression ||
            $node instanceof MemberAccessExpression
        ) {
            return $this->nodeToExpressionRange($node, $node);
        }
        if ($node2 = $node->getFirstAncestor(SubscriptExpression::class)) {
            return $this->nodeToExpressionRange($node2, $node2);
        }
        return null;
    }

    /**
     * @return array{expression:string,range:Range}
     */
    private function nodeToExpressionRange(Node|Token $token, Node $textNode): array
    {
        return [
                'expression' => (string)$token->getText($textNode->getFileContents()),
                'range' => $this->byteOffsetRangeForNode($token, $textNode),
            ];
    }

    /**
     * Converts Microsoft PhpParser Node to LSP Range.
     */
    private function byteOffsetRangeForNode(Node|Token $token, Node $textNode): Range
    {
        return new Range(
            PositionConverter::intByteOffsetToPosition($token->getStartPosition(), $textNode->getFileContents()),
            PositionConverter::intByteOffsetToPosition($token->getEndPosition(), $textNode->getFileContents()),
        );
    }
}
