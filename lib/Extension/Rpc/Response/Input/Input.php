<?php

namespace Phpactor\Extension\Rpc\Response\Input;

interface Input
{
    public function type(): string;

    public function name(): string;

    public function parameters(): array;
}
