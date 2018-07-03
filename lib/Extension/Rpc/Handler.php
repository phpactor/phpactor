<?php

namespace Phpactor\Extension\Rpc;

use Phpactor\Container\Schema;

interface Handler
{
    public function name(): string;

    public function configure(Schema $schema): void;

    public function handle(array $arguments);
}
