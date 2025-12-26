<?php

namespace Phpactor\Extension\LanguageServerPsalm\Model\Linter;

use function Amp\call;
use Amp\Delayed;
use Amp\Promise;
use Phpactor\Extension\LanguageServerPsalm\Model\Linter;
use Phpactor\LanguageServerProtocol\Diagnostic;

class TestLinter implements Linter
{
    /**
     * @param array<Diagnostic> $diagnostics
     */
    public function __construct(
        private readonly array $diagnostics,
        private readonly int $delay
    ) {
    }

    public function lint(string $url, ?string $text): Promise
    {
        return call(function () {
            yield new Delayed($this->delay);
            return $this->diagnostics;
        });
    }
}
