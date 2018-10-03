<?php

namespace Phpactor\Extension\Completion\LanguageServer;

use LanguageServerProtocol\CompletionOptions;
use LanguageServerProtocol\ServerCapabilities;
use LanguageServerProtocol\SignatureHelpOptions;
use Phpactor\Completion\Core\Completor;
use Phpactor\LanguageServer\Core\Extension;
use Phpactor\LanguageServer\Core\Handlers;
use Phpactor\LanguageServer\Core\Session\Manager;

class CompletionLanguageExtension implements Extension
{
    /**
     * @var Manager
     */
    private $sessionManager;

    /**
     * @var Completor
     */
    private $completor;

    public function __construct(Manager $sessionManager, Completor $completor)
    {
        $this->sessionManager = $sessionManager;
        $this->completor = $completor;
    }

    public function handlers(): Handlers
    {
        return new Handlers([
            new CompletionHandler($this->sessionManager, $this->completor),
        ]);
    }

    public function configureCapabilities(ServerCapabilities $capabilities): void
    {
        $capabilities->completionProvider = new CompletionOptions(false, [':', '>']);
        $capabilities->signatureHelpProvider = new SignatureHelpOptions(['(', ',']);
    }
}
