<?php

namespace Phpactor\Extension\LanguageServerPsalm\Model\Linter;

use Amp\Delayed;
use Amp\Promise;
use Phpactor\Extension\LanguageServerPsalm\Model\Linter;
use Phpactor\LanguageServerProtocol\Diagnostic;

class TestLinter implements Linter
{
    /**
     * @var array<Diagnostic>
     */
    private array $diagnostics;

    private int $delay;

    /**
     * @param array<Diagnostic> $diagnostics
     */
    public function __construct(array $diagnostics, int $delay)
    {
        $this->diagnostics = $diagnostics;
        $this->delay = $delay;
    }

    public function lint(string $url, ?string $text): Promise
    {
        return \Amp\call(function () {
            yield new Delayed($this->delay);
            return $this->diagnostics;
        });
    }
}
