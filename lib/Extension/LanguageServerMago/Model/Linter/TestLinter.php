<?php

namespace Phpactor\Extension\LanguageServerMago\Model\Linter;

use Amp\CancellationToken;
use Amp\Promise;
use Amp\Success;
use Phpactor\Extension\LanguageServerMago\Model\Linter;
use Phpactor\LanguageServerProtocol\Diagnostic;

class TestLinter implements Linter
{
    /**
     * @param array<Diagnostic> $diagnostics
     */
    public function __construct(private array $diagnostics = [])
    {
    }

    public function lint(string $url, string $text, CancellationToken $cancel): Promise
    {
        return new Success($this->diagnostics);
    }
}
