<?php

namespace Phpactor\Extension\Rpc;

interface Response
{
    public function name(): string;

    public function parameters(): array;
}
