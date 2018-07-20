<?php

namespace Phpactor\Extension\LanguageServer\Protocol;

class Message
{
    public $jsonrpc;

    public function __construct(string $jsonrpc)
    {
        $this->jsonrpc = $jsonrpc;
    }
}
