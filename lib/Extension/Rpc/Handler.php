<?php

namespace Phpactor\Extension\Rpc;

use Phpactor\MapResolver\Resolver;

interface Handler
{
    public function name(): string;

    public function configure(Resolver $schema): void;

    public function handle(array $arguments);
}
