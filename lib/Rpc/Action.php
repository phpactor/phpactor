<?php

namespace Phpactor\Rpc;

interface Action
{
    public function name(): string;

    public function parameters(): array;
}
