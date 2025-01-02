<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Model;

use Phpactor\LanguageServerProtocol\CodeDescription;
use Phpactor\LanguageServerProtocol\DiagnosticSeverity;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\Diagnostic;
use RuntimeException;

class DiagnosticsParser
{
    /**
     * @return array<Diagnostic>
     */
    public function parse(string $jsonString): array
    {
        $decoded = $this->decodeJson($jsonString);
        $diagnostics = [];

        foreach ($decoded['files'] ?? [] as $fileDiagnostics) {
            foreach ($fileDiagnostics['messages'] as $message) {
                $lineNo = (int)$message['line'] - 1;
                $lineNo = (int)$lineNo > 0 ? $lineNo : 0;
                $text = $message['message'];
                $diagnostics[] = new Diagnostic(
                    message: $text,
                    range: new Range(new Position($lineNo, 1), new Position($lineNo, 100)),
                    severity: DiagnosticSeverity::ERROR,
                    source: 'phpstan',
                    code: $message['identifier'] ?? null,
                );
                if (($message['tip'] ?? null) !== null) {
                    $diagnostics[] = new Diagnostic(
                        message: $message['tip'],
                        range: new Range(new Position($lineNo, 1), new Position($lineNo, 100)),
                        severity: DiagnosticSeverity::HINT,
                        source: 'phpstan',
                        codeDescription: $this->resolveCodeDescription($message),
                        code: $message['identifier'] ?? null,
                    );
                }
            }
        }

        return $diagnostics;
    }

    /**
     * @return array<mixed>
     */
    private function decodeJson(string $jsonString): array
    {
        $decoded = json_decode($jsonString, true);

        if (null === $decoded) {
            throw new RuntimeException(sprintf(
                'Could not decode expected PHPStan JSON string "%s"',
                $jsonString
            ));
        }

        return $decoded;
    }

    /**
     * @param array{tip?: string} $message
     */
    private function resolveCodeDescription(array $message): ?CodeDescription
    {
        $tip = $message['tip'] ?? null;
        if (null === $tip) {
            return null;
        }
        if (!preg_match('{(https?\://[^ ]+)$}', $tip, $matches)) {
            return null;
        }

        return new CodeDescription($matches[1]);
    }
}
