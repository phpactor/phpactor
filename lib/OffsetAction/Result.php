<?php

namespace Phpactor\OffsetAction;

interface Result
{
    public function action(): string;

    public function arguments(): array;
}
