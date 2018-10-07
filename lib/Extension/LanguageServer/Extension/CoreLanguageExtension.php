<?php

namespace Phpactor\Extension\LanguageServer\Extension;

use LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Core\Extension;
use Phpactor\LanguageServer\Core\Handlers;
use Phpactor\LanguageServer\Core\Session\Manager;

class CoreLanguageExtension implements Extension
{
    /**
     * @var Manager
     */
    private $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    public function handlers(): Handlers
    {
        return new Handlers([
            new DidChangeHandler($this->manager)
        ]);
    }

    public function configureCapabilities(ServerCapabilities $capabilities): void
    {
    }
}
