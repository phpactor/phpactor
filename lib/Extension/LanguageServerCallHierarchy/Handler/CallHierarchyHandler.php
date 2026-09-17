<?php

namespace Phpactor\Extension\LanguageServerCallHierarchy\Handler;

use Amp\Promise;
use Amp\Success;
use function Amp\call;
use function Amp\delay;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Microsoft\PhpParser\Node\Statement\FunctionDeclaration;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\Extension\LanguageServerBridge\Converter\RangeConverter;
use Phpactor\Extension\LanguageServerBridge\Converter\TextDocumentConverter;
use Phpactor\Extension\LanguageServerCallHierarchy\Inference\OutgoingCallsWalker;
use Phpactor\LanguageServerProtocol\CallHierarchyIncomingCall;
use Phpactor\LanguageServerProtocol\CallHierarchyItem;
use Phpactor\LanguageServerProtocol\CallHierarchyOutgoingCall;
use Phpactor\LanguageServerProtocol\CallHierarchyIncomingCallsParams;
use Phpactor\LanguageServerProtocol\CallHierarchyIncomingCallsRequest;
use Phpactor\LanguageServerProtocol\CallHierarchyOutgoingCallsParams;
use Phpactor\LanguageServerProtocol\CallHierarchyOutgoingCallsRequest;
use Phpactor\LanguageServerProtocol\CallHierarchyPrepareRequest;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServerProtocol\SymbolKind;
use Phpactor\LanguageServerProtocol\TextDocumentPositionParams;
use Phpactor\LanguageServer\Core\Workspace\Exception\UnknownDocument;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\ReferenceFinder\Exception\CouldNotLocateDefinition;
use Phpactor\ReferenceFinder\ReferenceFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\Location;
use Phpactor\WorseReflection\Core\AstProvider;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\Reflector;

class CallHierarchyHandler implements Handler, CanRegisterCapabilities
{
    public function __construct(
        private Workspace $workspace,
        private Reflector $reflector,
        private ReferenceFinder $referenceFinder,
        private AstProvider $astProvider,
        private DefinitionLocator $definitionLocator,
    ) {
    }

    public function methods(): array
    {
        return [
            CallHierarchyPrepareRequest::METHOD => 'prepareCallHierarchy',
            CallHierarchyIncomingCallsRequest::METHOD => 'incomingCalls',
            CallHierarchyOutgoingCallsRequest::METHOD => 'outgoingCalls',
        ];
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities): void
    {
        $capabilities->callHierarchyProvider = true;
    }

    /**
     * @return Promise<CallHierarchyItem[]|null>
     */
    public function prepareCallHierarchy(TextDocumentPositionParams $params): Promise
    {
        $lspDoc = $this->workspace->get($params->textDocument->uri);
        $doc = TextDocumentConverter::fromLspTextItem($lspDoc);
        $offset = PositionConverter::positionToByteOffset($params->position, $lspDoc->text);

        $reflection = $this->reflector->reflectOffset($doc, $offset);
        $symbol = $reflection->nodeContext()->symbol();

        if (!$symbol->isKnown()) {
            return new Success(null);
        }

        return new Success([$this->buildItem($symbol, $params->textDocument->uri, $lspDoc->text)]);
    }

    /**
     * @return Promise<CallHierarchyIncomingCall[]>
     */
    public function incomingCalls(CallHierarchyIncomingCallsParams $params): Promise
    {
        $item = $params->item;
        $lspDoc = $this->workspace->get($item->uri);
        $doc = TextDocumentConverter::fromLspTextItem($lspDoc);
        $offset = PositionConverter::positionToByteOffset($item->selectionRange->start, $lspDoc->text);

        $promise = call(function () use ($doc, $offset) {
            $incomingCalls = [];
            foreach ($this->referenceFinder->findReferences($doc, $offset) as $potentialLocation) {
                if (!$potentialLocation->isSurely()) {
                    continue;
                }
                $location = $potentialLocation->location();
                $fromItem = $this->buildCallerItem($location);
                $fromRanges = [
                    RangeConverter::toLspRange($location->range(), $this->workspace->get($location->uri()->__toString())->text),
                ];
                $incomingCalls[] = new CallHierarchyIncomingCall($fromItem, $fromRanges);
            }
            return $incomingCalls;
        });
        /** @var \Amp\Promise<CallHierarchyIncomingCall[]> $promise */
        return $promise;
    }

    /**
     * @return Promise<CallHierarchyOutgoingCall[]>
     */
    public function outgoingCalls(CallHierarchyOutgoingCallsParams $params): Promise
    {
        $item = $params->item;
        $lspDoc = $this->workspace->get($item->uri);
        $doc = TextDocumentConverter::fromLspTextItem($lspDoc);
        $offset = PositionConverter::positionToByteOffset($item->selectionRange->start, $lspDoc->text);

        return call(function () use ($item, $lspDoc, $doc, $offset) {
            $ast = $this->astProvider->get($doc);
            $bodyRange = $this->enclosingFunctionBodyRange($ast, $offset->toInt());
            if (null === $bodyRange) {
                return [];
            }

            $walker = new OutgoingCallsWalker($bodyRange);
            foreach ($this->reflector->walk($doc, $walker) as $tick) {
                yield delay(0);
            }

            $outgoingCalls = [];
            foreach ($walker->calls() as $call) {
                $toItem = $this->buildCalleeItem($call->name, $call->kind, $call->offset, $item->uri, $lspDoc->text);
                $fromRanges = [
                    RangeConverter::toLspRange(
                        ByteOffsetRange::fromInts($call->node->getStartPosition(), $call->node->getEndPosition()),
                        $lspDoc->text
                    ),
                ];
                $outgoingCalls[] = new CallHierarchyOutgoingCall($toItem, $fromRanges);
            }
            return $outgoingCalls;
        });
    }

    private function buildItem(Symbol $symbol, string $uri, string $text): CallHierarchyItem
    {
        $range = RangeConverter::toLspRange($symbol->position(), $text);
        $kind = match ($symbol->symbolType()) {
            Symbol::CLASS_ => SymbolKind::CLASS_,
            Symbol::METHOD => SymbolKind::METHOD,
            Symbol::FUNCTION => SymbolKind::FUNCTION,
            Symbol::PROPERTY => SymbolKind::PROPERTY,
            Symbol::CONSTANT, Symbol::DECLARED_CONSTANT, Symbol::CASE => SymbolKind::CONSTANT,
            Symbol::VARIABLE => SymbolKind::VARIABLE,
            Symbol::STRING => SymbolKind::STRING,
            Symbol::NUMBER => SymbolKind::NUMBER,
            Symbol::BOOLEAN => SymbolKind::BOOLEAN,
            Symbol::ARRAY => SymbolKind::ARRAY,
            default => SymbolKind::FUNCTION,
        };
        return new CallHierarchyItem(
            $symbol->name(),
            $kind,
            $uri,
            $range,
            $range,
            null,
            sprintf('%s %s', $symbol->symbolType(), $symbol->name()),
        );
    }

    private function buildCallerItem(Location $location): CallHierarchyItem
    {
        $uri = $location->uri()->__toString();
        try {
            $lspDoc = $this->workspace->get($uri);
        } catch (UnknownDocument) {
            $range = RangeConverter::toLspRange($location->range(), '');
            return new CallHierarchyItem('unknown', SymbolKind::FUNCTION, $uri, $range, $range);
        }

        $doc = TextDocumentConverter::fromLspTextItem($lspDoc);
        $offset = ByteOffset::fromInt($location->range()->start()->toInt());
        $ast = $this->astProvider->get($doc);
        $enclosing = $this->enclosingFunctionLike($ast, $offset->toInt());
        if (null === $enclosing) {
            $range = RangeConverter::toLspRange($location->range(), $lspDoc->text);
            return new CallHierarchyItem('unknown', SymbolKind::FUNCTION, $uri, $range, $range);
        }

        $name = NodeUtil::nameFromTokenOrNode($enclosing, $enclosing->name);
        $kind = $enclosing instanceof MethodDeclaration ? SymbolKind::METHOD : SymbolKind::FUNCTION;
        $fullRange = RangeConverter::toLspRange(
            ByteOffsetRange::fromInts($enclosing->getStartPosition(), $enclosing->getEndPosition()),
            $lspDoc->text
        );
        $nameRange = $enclosing->name
            ? RangeConverter::toLspRange(
                ByteOffsetRange::fromInts($enclosing->name->getStartPosition(), $enclosing->name->getEndPosition()),
                $lspDoc->text
            )
            : $fullRange;
        return new CallHierarchyItem($name, $kind, $uri, $fullRange, $nameRange);
    }

    private function buildCalleeItem(string $name, string $kind, int $offset, string $uri, string $text): CallHierarchyItem
    {
        $doc = TextDocumentConverter::fromLspTextItem($this->workspace->get($uri));

        try {
            $locations = $this->definitionLocator->locateDefinition($doc, ByteOffset::fromInt($offset));
            $location = $locations->first()->location();
            $targetUri = $location->uri()->__toString();
            try {
                $targetText = $this->workspace->get($targetUri)->text;
            } catch (UnknownDocument) {
                $range = RangeConverter::toLspRange(ByteOffsetRange::fromInts($offset, $offset), $text);
                return new CallHierarchyItem($name, $kind === 'method' ? SymbolKind::METHOD : SymbolKind::FUNCTION, $uri, $range, $range);
            }
            $range = RangeConverter::toLspRange($location->range(), $targetText);
            return new CallHierarchyItem($name, $kind === 'method' ? SymbolKind::METHOD : SymbolKind::FUNCTION, $targetUri, $range, $range);
        } catch (CouldNotLocateDefinition) {
            $range = RangeConverter::toLspRange(ByteOffsetRange::fromInts($offset, $offset), $text);
            return new CallHierarchyItem($name, $kind === 'method' ? SymbolKind::METHOD : SymbolKind::FUNCTION, $uri, $range, $range);
        }
    }

    /**
     * Find the innermost function-like node (method or function) whose body
     * contains the given byte offset.
     *
     * @return MethodDeclaration|FunctionDeclaration|null
     */
    private function enclosingFunctionLike(Node $ast, int $offset): ?Node
    {
        $enclosing = null;
        foreach ($ast->getDescendantNodes() as $node) {
            if (!$node instanceof MethodDeclaration && !$node instanceof FunctionDeclaration) {
                continue;
            }
            if ($node->getStartPosition() <= $offset && $offset <= $node->getEndPosition()) {
                $enclosing = $node;
            }
        }
        return $enclosing;
    }

    private function enclosingFunctionBodyRange(Node $ast, int $offset): ?ByteOffsetRange
    {
        $enclosing = $this->enclosingFunctionLike($ast, $offset);
        if (null === $enclosing) {
            return null;
        }
        $body = $enclosing->compoundStatementOrSemicolon;
        if (!$body instanceof CompoundStatementNode) {
            return null;
        }
        return ByteOffsetRange::fromInts($body->getStartPosition(), $body->getEndPosition());
    }
}
