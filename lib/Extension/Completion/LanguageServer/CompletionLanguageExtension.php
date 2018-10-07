<?php

namespace Phpactor\Extension\Completion\LanguageServer;

use LanguageServerProtocol\CompletionOptions;
use LanguageServerProtocol\ServerCapabilities;
use LanguageServerProtocol\SignatureHelpOptions;
use Phpactor\Completion\Core\Completor;
use Phpactor\LanguageServer\Core\Extension;
use Phpactor\LanguageServer\Core\Handlers;
use Phpactor\LanguageServer\Core\Session\Manager;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;

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

    /**
     * @var SourceCodeReflector
     */
    private $reflector;

    public function __construct(Manager $sessionManager, Completor $completor, SourceCodeReflector $reflector)
    {
        $this->sessionManager = $sessionManager;
        $this->completor = $completor;
        $this->reflector = $reflector;
    }

    public function handlers(): Handlers
    {
        return new Handlers([
            new CompletionHandler($this->sessionManager, $this->completor, $this->reflector),
        ]);
    }

    public function configureCapabilities(ServerCapabilities $capabilities): void
    {
        $capabilities->completionProvider = new CompletionOptions(false, [':', '>']);
        $capabilities->signatureHelpProvider = new SignatureHelpOptions(['(', ',']);
    }
}
