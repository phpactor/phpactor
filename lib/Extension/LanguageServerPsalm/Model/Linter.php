<?php

namespace Phpactor\Extension\LanguageServerPsalm\Model;

use Amp\Promise;
use Phpactor\LanguageServerProtocol\Diagnostic;

interface Linter
{
    /**
     * @return Promise<array<Diagnostic>>
     */
    public function lint(string $url, ?string $text): Promise;
}
