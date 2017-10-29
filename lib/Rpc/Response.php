<?php

namespace Phpactor\Rpc;

interface Response
{
    public function name(): string;

    public function parameters(): array;
}
