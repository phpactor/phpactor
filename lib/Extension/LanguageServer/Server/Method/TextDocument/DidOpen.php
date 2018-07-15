<?php

namespace Phpactor\Extension\LanguageServer\Server\Method\TextDocument;

use Phpactor\Extension\LanguageServer\Server\Method;
use Phpactor\MapResolver\Resolver;

class DidOpen implements Method
{
    public function name(): string
    {
        return 'textDocument/didOpen';
    }

    public function configure(Resolver $resolver)
    {
        $sch
    }

    public function handle(array $params)
    {
    }
}
