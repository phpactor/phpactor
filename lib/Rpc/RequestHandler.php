<?php

namespace Phpactor\Rpc;

interface RequestHandler
{
    public function handle(ActionRequest $request): Response;
}
