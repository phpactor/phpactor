<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Converter;

use Phpactor\CodeTransform\Domain\Diagnostic;
use Phpactor\CodeTransform\Domain\Diagnostics;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\LanguageServerProtocol\Diagnostic as LspDiagnostic;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\TextDocument\TextDocument;

final class DiagnosticsConverter
{
    public static function toLspDiagnostics(TextDocument $textDocument, Diagnostics $diagnostics): array
    {
        $lspDiagnostics = [];
        foreach ($diagnostics as $diagnostic) {
            $lspDiagnostics[] = self::toLspDiagnostic($textDocument, $diagnostic);
        }

        return $lspDiagnostics;
    }

    public static function toLspDiagnostic(TextDocument $textDocument, Diagnostic $diagnostic): LspDiagnostic
    {
        return LspDiagnostic::fromArray([
            'range' => new Range(
                PositionConverter::byteOffsetToPosition($diagnostic->range()->start(), $textDocument->__toString()),
                PositionConverter::byteOffsetToPosition($diagnostic->range()->end(), $textDocument->__toString())
            ),
            'message' => $diagnostic->message(),
            'source' => 'phpactor',
            'severity' => $diagnostic->severity()
        ]);
    }
}
