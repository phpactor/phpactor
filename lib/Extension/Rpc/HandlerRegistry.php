<?php

namespace Phpactor\Extension\Rpc;

interface HandlerRegistry
{
    public function get($handlerName): Handler;
}
