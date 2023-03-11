<?php

namespace Phpactor\Extension\LanguageServerWorseReflection\DiagnosticProvider;

use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\LanguageServerProtocol\DiagnosticSeverity as LanguageServerProtocolDiagnosticSeverity;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;
use Phpactor\WorseReflection\Reflector;
use function Amp\call;

class WorseDiagnosticProvider implements DiagnosticsProvider
{
    public function __construct(private Reflector $reflector)
    {
    }

    public function provideDiagnostics(TextDocumentItem $textDocument, CancellationToken $cancel): Promise
    {
        return call(function () use ($textDocument, $cancel) {
            $lspDiagnostics = [];
            foreach ($this->reflector->diagnostics($textDocument->text) as $diagnostic) {
                $range = new Range(
                    PositionConverter::byteOffsetToPosition($diagnostic->range()->start(), $textDocument->text),
                    PositionConverter::byteOffsetToPosition($diagnostic->range()->end(), $textDocument->text),
                );
                $lspDiagnostic = ProtocolFactory::diagnostic($range, $diagnostic->message());
                $lspDiagnostic->severity = self::toLspSeverity($diagnostic->severity());
                $lspDiagnostics[] = $lspDiagnostic;

                if ($cancel->isRequested()) {
                    return $lspDiagnostics;
                }
            }

            return $lspDiagnostics;
        });
    }

    public function name(): string
    {
        return 'worse';
    }

    /**
     * @return LanguageServerProtocolDiagnosticSeverity::*
     */
    private static function toLspSeverity(DiagnosticSeverity $diagnosticSeverity): int
    {
        if ($diagnosticSeverity->isError()) {
            return LanguageServerProtocolDiagnosticSeverity::ERROR;
        }

        if ($diagnosticSeverity->isWarning()) {
            return LanguageServerProtocolDiagnosticSeverity::WARNING;
        }
        if ($diagnosticSeverity->isHint()) {
            return LanguageServerProtocolDiagnosticSeverity::HINT;
        }

        return LanguageServerProtocolDiagnosticSeverity::INFORMATION;
    }
}
