<?php

namespace Phpactor\Extension\LanguageServer\Server;

use Phpactor\Extension\LanguageServer\Protocol\ResponseMessage;

interface Dispatcher
{
    public function dispatch(string $method, array $arguments): ResponseMessage;
}
