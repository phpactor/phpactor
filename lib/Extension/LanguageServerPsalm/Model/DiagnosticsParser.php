<?php

namespace Phpactor\Extension\LanguageServerPsalm\Model;

use Phpactor\LanguageServerProtocol\DiagnosticSeverity;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Psalm\Config;
use RuntimeException;

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
     * @return array<mixed>
     */
    private function decodeJson(string $jsonString): array
    {
        $decoded = json_decode($jsonString, true, JSON_THROW_ON_ERROR);

        if (null === $decoded) {
            throw new RuntimeException(sprintf(
                'Could not decode Psalm JSON output "%s": %s',
                $jsonString,
                json_last_error_msg(),
            ));
        }

        return $decoded;
    }

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
