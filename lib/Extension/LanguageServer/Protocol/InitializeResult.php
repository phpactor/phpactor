<?php

namespace Phpactor\Extension\LanguageServer\Protocol;

class InitializeResult
{
    /**
     * @var ServerCapabilities
     */
    public $capabilities;

    public function __construct(ServerCapabilities $capabilities)
    {
        $this->capabilities = $capabilities;
    }
}
