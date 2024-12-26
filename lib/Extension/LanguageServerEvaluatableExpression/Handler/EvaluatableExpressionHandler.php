<?php

namespace Phpactor\Extension\LanguageServerEvaluatableExpression\Handler;

use Amp\Success;
use Amp\Promise;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Token;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\Extension\LanguageServerBridge\Converter\RangeConverter;
use Phpactor\Extension\LanguageServerEvaluatableExpression\Protocol\EvaluatableExpression;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServerProtocol\TextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\TextDocument\ByteOffsetRange;
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
        $capabilities->experimental['xevaluatableExpressionProvider'] = true;
    }

    private function nodeToEvaluatable(Node $node): ?EvaluatableExpression
    {
        if ($node instanceof Node\Parameter) {
            return
                new EvaluatableExpression(
                    expression: $node->variableName->getText($node->getFileContents()),
                    range: $this->byteOffsetRangeForNode($node->variableName, $node),
                );
        }
        if (
            $node instanceof Node\Expression\Variable ||
            $node instanceof Node\Expression\SubscriptExpression ||
            $node instanceof Node\Expression\MemberAccessExpression ||
            (($node2 = $node->getFirstAncestor(Node\Expression\SubscriptExpression::class)) && ($node = $node2))
        ) {
            return
                new EvaluatableExpression(
                    expression: $node->getText(),
                    range: $this->byteOffsetRangeForNode($node, $node),
                );
        }
        return null;
    }

    /**
     * Converts Microsoft PhpParser Node to LSP Range.
     */
    private function byteOffsetRangeForNode(Node|Token $token, Node $textNode): Range
    {
        // Note: Cloud have usexd NodeUtil::byteOffsetRangeForNode but it's limited to Variable
        $boRange = ByteOffsetRange::fromInts($token->getStartPosition(), $token->getEndPosition());
        return RangeConverter::toLspRange($boRange, $textNode->getFileContents());
    }
}
