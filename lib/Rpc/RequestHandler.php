<?php

namespace Phpactor\Rpc;

use Phpactor\Rpc\Response;
use Phpactor\Rpc\Request;

interface RequestHandler
{
    public function handle(Request $request): Response;
}
