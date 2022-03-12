<?php

namespace Phpactor\Extension\LanguageServerPsalm\Tests\Util;

use Phpactor\LanguageServerProtocol\DiagnosticSeverity;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\Diagnostic;

final class DiagnosticBuilder
{
    public static function create(): self
    {
        return new self();
    }

    public function build(): Diagnostic
    {
        return Diagnostic::fromArray([
            'message' => 'Undefined variable: $barfoo',
            'range' =>  new Range(
                new Position(1, 1),
                new Position(1, 1)
            ),
            'severity' => DiagnosticSeverity::ERROR,
            'source' => 'phpstan'
        ]);
    }
}
