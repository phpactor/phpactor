<?php

namespace Phpactor\Rpc\Editor\Input;

interface Input
{
    public function type(): string;

    public function name(): string;

    public function parameters(): array;
}
