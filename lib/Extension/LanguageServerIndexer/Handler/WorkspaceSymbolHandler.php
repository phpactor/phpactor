<?php

namespace Phpactor\Extension\LanguageServerIndexer\Handler;

use Amp\Promise;
use Phpactor\Extension\LanguageServerIndexer\Model\WorkspaceSymbolProvider;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServerProtocol\SymbolInformation;
use Phpactor\LanguageServerProtocol\WorkspaceSymbolParams;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;

class WorkspaceSymbolHandler implements Handler, CanRegisterCapabilities
{
    /**
     * @var WorkspaceSymbolProvider
     */
    private $provider;

    public function __construct(WorkspaceSymbolProvider $provider)
    {
        $this->provider = $provider;
    }

    public function methods(): array
    {
        return [
            'workspace/symbol' => 'symbol',
        ];
    }

    /**
     * @return Promise<SymbolInformation[]>
     */
    public function symbol(
        WorkspaceSymbolParams $params
    ): Promise {
        return \Amp\call(function () use ($params) {
            return $this->provider->provideFor($params->query);
        });
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities): void
    {
        $capabilities->workspaceSymbolProvider = true;
    }
}
