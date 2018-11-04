<?php

namespace Phpactor\Extension\WorseReflectionExtra\LanguageServer;

use LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Core\Extension;
use Phpactor\LanguageServer\Core\Handlers;
use Phpactor\LanguageServer\Core\Session\Manager;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;

class WorseReflectionLanguageExtension implements Extension
{
    /**
     * @var Manager
     */
    private $sessionManager;

    /**
     * @var SourceCodeReflector
     */
    private $reflector;

    public function __construct(Manager $sessionManager, SourceCodeReflector $reflector)
    {
        $this->sessionManager = $sessionManager;
        $this->reflector = $reflector;
    }

    public function handlers(): Handlers
    {
        return new Handlers([
            new GotoDefinitionHandler($this->sessionManager, $this->reflector),
        ]);
    }

    public function configureCapabilities(ServerCapabilities $capabilities): void
    {
        $capabilities->definitionProvider = true;
    }
}
