<?php

namespace Phpactor\Extension\LanguageServerMago\Model;

use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\LanguageServerProtocol\Diagnostic;

interface Linter
{
    /**
     * @return Promise<array<Diagnostic>>
     */
    public function lint(string $url, string $text, CancellationToken $cancel): Promise;
}
