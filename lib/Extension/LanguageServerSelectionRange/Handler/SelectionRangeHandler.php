<?php

namespace Phpactor\Extension\LanguageServerSelectionRange\Handler;

use Amp\Promise;
use Amp\Success;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\Extension\LanguageServerBridge\Converter\TextDocumentConverter;
use Phpactor\Extension\LanguageServerSelectionRange\Model\RangeProvider;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\SelectionRange;
use Phpactor\LanguageServerProtocol\SelectionRangeParams;
use Phpactor\LanguageServerProtocol\SelectionRangeRequest;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Workspace\Workspace;

class SelectionRangeHandler implements Handler, CanRegisterCapabilities
{
    public function __construct(
        private Workspace $workspace,
        private RangeProvider $provider
    ) {
    }


    public function methods(): array
    {
        return [
            SelectionRangeRequest::METHOD => 'selectionRange',
        ];
    }

    /**
     * @return Promise<SelectionRange[]|null>
     */
    public function selectionRange(SelectionRangeParams $params): Promise
    {
        $textDocument = $this->workspace->get($params->textDocument->uri);
        $offsets = array_map(function (Position $position) use ($textDocument) {
            return PositionConverter::positionToByteOffset($position, $textDocument->text);
        }, $params->positions);

        return new Success($this->provider->provide(
            TextDocumentConverter::fromLspTextItem($textDocument),
            $offsets
        ));
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities): void
    {
        $capabilities->selectionRangeProvider = true;
    }
}
