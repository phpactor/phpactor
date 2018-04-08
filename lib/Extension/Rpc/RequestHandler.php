<?php

namespace Phpactor\Extension\Rpc;

interface RequestHandler
{
    public function handle(Request $request): Response;
}
