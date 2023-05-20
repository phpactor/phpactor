<?php

namespace Phpactor\Extension\LanguageServerPsalm\Model;

use JsonException;
use Phpactor\LanguageServerProtocol\DiagnosticSeverity;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Psalm\Config;
use RuntimeException;

/**
 * @phpstan-type PsalmDiagnostic array{
 *   "severity":string,
 *   "line_from":int,
 *   "line_to":int,
 *   "type":string,
 *   "message":string,
 *   "file_name":string,
 *   "file_path":string,
 *   "snippet":string,
 *   "selected_text":string,
 *   "from":int,
 *   "to":int,
 *   "snippet_from":int,
 *   "snippet_to":int,
 *   "column_from":int,
 *   "column_to":int,
 *   "error_level":int,
 *   "shortcode":int,
 *   "link":string,
 *   "taint_trace":mixed
 * }
*/

class DiagnosticsParser
{
    /**
     * @return array<Diagnostic>
     */
    public function parse(string $jsonString, string $filename): array
    {
        $decoded = $this->decodeJson($jsonString);
        $diagnostics = [];

        foreach ($decoded as $psalmDiagnostic) {
            if ($psalmDiagnostic['file_path'] !== $filename) {
                continue;
            }

            $diagnostics[] = Diagnostic::fromArray([
                'message' => $psalmDiagnostic['message'],
                'range' => new Range(
                    new Position($psalmDiagnostic['line_from'] - 1, $psalmDiagnostic['column_from'] - 1),
                    new Position($psalmDiagnostic['line_to'] - 1, $psalmDiagnostic['column_to'] - 1)
                ),
                'severity' => $this->errorLevel($psalmDiagnostic),
                'source' => 'psalm'
            ]);
        }

        return $diagnostics;
    }

    /**
     * @return array<PsalmDiagnostic>
     */
    private function decodeJson(string $jsonString): array
    {

        try {
            /** @var array<PsalmDiagnostic> $decoded */
            $decoded = json_decode($jsonString, true, flags: JSON_THROW_ON_ERROR);
            return $decoded;
        } catch(JsonException $e) {
            throw new RuntimeException(sprintf(
                'Could not decode Psalm JSON output "%s": %s',
                $jsonString,
                $e->getMessage()
            ));
        }
    }

    /**
     * @param PsalmDiagnostic $psalmDiagnostic
     */
    private function errorLevel(array $psalmDiagnostic): int
    {
        switch ($psalmDiagnostic['severity']) {
            case Config::REPORT_ERROR:
                return DiagnosticSeverity::ERROR;
            case Config::REPORT_INFO:
                return DiagnosticSeverity::WARNING;
        }

        return DiagnosticSeverity::INFORMATION;
    }
}
