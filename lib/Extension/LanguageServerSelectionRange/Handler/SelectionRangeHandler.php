<?php

namespace Phpactor\Extension\LanguageServerSelectionRange\Handler;

use Amp\Promise;
use Amp\Success;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
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
    /**
     * @var Workspace
     */
    private $workspace;

    /**
     * @var RangeProvider
     */
    private $provider;

    public function __construct(Workspace $workspace, RangeProvider $provider)
    {
        $this->workspace = $workspace;
        $this->provider = $provider;
    }

    /**
     * {@inheritDoc}
     */
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

        return new Success($this->provider->provide($textDocument->text, $offsets));
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities): void
    {
        $capabilities->selectionRangeProvider = true;
    }
}
