<?php

namespace Phpactor\Extension\Rpc;

use Phpactor\Extension\Rpc\Request;

interface RequestHandler
{
    public function handle(Request $request): Response;
}
