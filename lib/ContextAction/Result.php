<?php

namespace Phpactor\ContextAction;

interface Result
{
    public function action(): string;

    public function arguments(): array;
}
