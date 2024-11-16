<?php

namespace Phpactor\Extension\LanguageServerWorseReflection\DiagnosticProvider;

use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\Extension\LanguageServerBridge\Converter\RangeConverter;
use Phpactor\Extension\LanguageServerBridge\Converter\TextDocumentConverter;
use Phpactor\LanguageServerProtocol\DiagnosticSeverity as LanguageServerProtocolDiagnosticSeverity;
use Phpactor\LanguageServerProtocol\DiagnosticTag;
use Phpactor\WorseReflection\Core\DiagnosticTag as PhpactorDiagnosticTag;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\DeprecatedUsageDiagnostic;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\UnusedImportDiagnostic;
use Phpactor\WorseReflection\Core\Diagnostic;
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
            foreach (yield $this->reflector->diagnostics(TextDocumentConverter::fromLspTextItem($textDocument)) as $diagnostic) {
                /** @var Diagnostic $diagnostic */
                $range = RangeConverter::toLspRange($diagnostic->range(), $textDocument->text);

                $lspDiagnostic = ProtocolFactory::diagnostic($range, $diagnostic->message());
                $lspDiagnostic->severity = self::toLspSeverity($diagnostic->severity());
                $lspDiagnostic->source = 'phpactor';
                $lspDiagnostic->tags = self::toLspTags($diagnostic->tags());
                $lspDiagnostic->code = 'worse.'.$diagnostic->code();

                if ($diagnostic instanceof DeprecatedUsageDiagnostic) {
                    $lspDiagnostic->tags[] = DiagnosticTag::DEPRECATED;
                }

                if ($diagnostic instanceof UnusedImportDiagnostic) {
                    $lspDiagnostic->tags[] = DiagnosticTag::UNNECESSARY;
                }

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
    /**
    * @param array<PhpactorDiagnosticTag> $tags
    *
    * @return array<DiagnosticTag::*>
    */
    private static function toLspTags(array $tags): array
    {
        return array_map(
            fn ($tag) => match($tag) {
                PhpactorDiagnosticTag::DEPRECATED => DiagnosticTag::DEPRECATED,
                PhpactorDiagnosticTag::UNNECESSARY => DiagnosticTag::UNNECESSARY,
            },
            $tags
        );
    }
}
