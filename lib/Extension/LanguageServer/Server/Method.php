<?php

namespace Phpactor\Extension\LanguageServer\Server;

use Phpactor\MapResolver\Resolver;

interface Method
{
    public function name(): string;

    public function configure(Resolver $schema);

    public function handle(array $params);
}
