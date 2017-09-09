<?php

namespace Phpactor\Rpc;

interface Action
{
    public function name(): string;

    public function defaultParameters(): array;

    public function perform(array $arguments): Response;
}
