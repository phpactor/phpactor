<?php

namespace Phpactor\Rpc;

interface Response
{
    public function action(): string;

    public function parameters(): array;
}
