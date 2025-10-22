<?php

namespace Phpactor\Extension\LanguageServerEvaluatableExpression\Handler;

use Amp\Success;
use Amp\Promise;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Token;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\Extension\LanguageServerEvaluatableExpression\Protocol\EvaluatableExpression;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServerProtocol\TextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\LanguageServerProtocol\Range;

class EvaluatableExpressionHandler implements Handler, CanRegisterCapabilities
{
    public function __construct(
        private Workspace $workspace,
        private Parser $parser,
    ) {
    }

    public function methods(): array
    {
        return [
            'textDocument/xevaluatableExpression' => 'xevaluatableExpression',
        ];
    }

    /**
     * @return Promise<EvaluatableExpression|null>
     */
    public function xevaluatableExpression(
        TextDocumentIdentifier $textDocument,
        Position $position
    ): Promise {
        $document = $this->workspace->get($textDocument->uri);
        $offset = PositionConverter::positionToByteOffset($position, $document->text);
        $document = TextDocumentBuilder::create($document->text)
            ->uri($document->uri)
            ->language('php')
            ->build();

        $char = substr($document, $offset->toInt(), 1);

        // do not provide evaluatable for whitespace
        if (trim($char) == '') {
            return new Success(null);
        }

        $rootNode = $this->parser->parseSourceFile((string) $document, $document->uri()?->__toString());
        $node = $rootNode->getDescendantNodeAtPosition($offset->toInt());
        return new Success($this->nodeToEvaluatable($node));
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities): void
    {
        $capabilities->experimental ??= [];
        // @phpstan-ignore offsetAccess.nonOffsetAccessible
        $capabilities->experimental['xevaluatableExpressionProvider'] = true;
    }

    private function nodeToEvaluatable(Node $node): ?EvaluatableExpression
    {
        if ($node instanceof Node\Parameter) {
            return $this->evaluatableExpressionForNode($node->variableName, $node);
        }
        if (
            $node instanceof Node\Expression\Variable ||
            $node instanceof Node\Expression\SubscriptExpression ||
            $node instanceof Node\Expression\MemberAccessExpression
        ) {
            return $this->evaluatableExpressionForNode($node, $node);
        }
        if ($node2 = $node->getFirstAncestor(Node\Expression\SubscriptExpression::class)) {
            return $this->evaluatableExpressionForNode($node2, $node2);
        }
        return null;
    }

    private function evaluatableExpressionForNode(Node|Token $token, Node $textNode): EvaluatableExpression
    {
        return
            new EvaluatableExpression(
                expression: (string)$token->getText($textNode->getFileContents()),
                range: $this->byteOffsetRangeForNode($token, $textNode),
            );
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
