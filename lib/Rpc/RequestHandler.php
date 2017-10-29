<?php

namespace Phpactor\Rpc;

interface RequestHandler
{
    public function handle(Request $request): Response;
}
