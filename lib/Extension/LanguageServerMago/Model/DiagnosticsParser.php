<?php

namespace Phpactor\Extension\LanguageServerMago\Model;

use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\DiagnosticRelatedInformation;
use Phpactor\LanguageServerProtocol\DiagnosticSeverity;
use Phpactor\LanguageServerProtocol\Location;
use Phpactor\LanguageServerProtocol\Range;
use RuntimeException;

/**
 * Converts the JSON produced by `mago analyze|lint --reporting-format=json`
 * into LSP diagnostics.
 *
 * Mago reports byte offsets (not columns) and 0-indexed lines, so positions are
 * derived from the byte offsets against the analysed document text. Only issues
 * anchored to the current document are emitted: a single Mago run may report
 * issues for other files, but a provider call publishes for one document.
 */
class DiagnosticsParser
{
    /**
     * @return array<Diagnostic>
     */
    public function parse(
        string $jsonString,
        string $documentText,
        string $source,
        string $relativePath,
        string $documentUri
    ): array {
        $decoded = $this->decodeJson($jsonString);
        $issues = is_array($decoded['issues'] ?? null) ? $decoded['issues'] : [];

        $diagnostics = [];
        foreach ($issues as $issue) {
            if (!is_array($issue)) {
                continue;
            }
            $diagnostic = $this->parseIssue($issue, $documentText, $source, $relativePath, $documentUri);
            if (null !== $diagnostic) {
                $diagnostics[] = $diagnostic;
            }
        }

        return $diagnostics;
    }

    /**
     * @param array<mixed> $issue
     */
    private function parseIssue(
        array $issue,
        string $documentText,
        string $source,
        string $relativePath,
        string $documentUri
    ): ?Diagnostic {
        $annotations = is_array($issue['annotations'] ?? null) ? $issue['annotations'] : [];

        $primary = $this->primaryAnnotation($annotations);
        if (null === $primary) {
            return null;
        }

        // Drop issues whose primary location is in another file: the provider
        // publishes diagnostics for the current document only.
        if ($this->annotationName($primary) !== $relativePath) {
            return null;
        }

        return new Diagnostic(
            range: $this->annotationRange($primary, $documentText),
            message: $this->composeMessage($issue),
            severity: $this->severity(is_string($issue['level'] ?? null) ? $issue['level'] : ''),
            code: is_string($issue['code'] ?? null) ? $issue['code'] : null,
            source: $source,
            relatedInformation: $this->relatedInformation($annotations, $documentText, $relativePath, $documentUri),
        );
    }

    /**
     * @param array<mixed> $annotations
     * @return array<mixed>|null
     */
    private function primaryAnnotation(array $annotations): ?array
    {
        foreach ($annotations as $annotation) {
            if (is_array($annotation) && ($annotation['kind'] ?? null) === 'Primary') {
                return $annotation;
            }
        }

        $first = $annotations[0] ?? null;

        return is_array($first) ? $first : null;
    }

    /**
     * Secondary annotations that point inside the current document become
     * related information. Cross-file secondaries are skipped: their byte
     * offsets index a file whose text is not available here.
     *
     * @param array<mixed> $annotations
     * @return array<DiagnosticRelatedInformation>|null
     */
    private function relatedInformation(
        array $annotations,
        string $documentText,
        string $relativePath,
        string $documentUri
    ): ?array {
        $related = [];
        foreach ($annotations as $annotation) {
            if (!is_array($annotation) || ($annotation['kind'] ?? null) !== 'Secondary') {
                continue;
            }
            if ($this->annotationName($annotation) !== $relativePath) {
                continue;
            }
            $message = is_string($annotation['message'] ?? null) ? $annotation['message'] : 'related';
            $related[] = new DiagnosticRelatedInformation(
                new Location($documentUri, $this->annotationRange($annotation, $documentText)),
                $message,
            );
        }

        return $related === [] ? null : $related;
    }

    /**
     * @param array<mixed> $annotation
     */
    private function annotationRange(array $annotation, string $documentText): Range
    {
        $span = is_array($annotation['span'] ?? null) ? $annotation['span'] : [];
        $start = $this->offset($span['start'] ?? null, 0);
        $end = $this->offset($span['end'] ?? null, $start);

        return new Range(
            PositionConverter::intByteOffsetToPosition($start, $documentText),
            PositionConverter::intByteOffsetToPosition($end, $documentText),
        );
    }

    private function offset(mixed $position, int $default): int
    {
        if (is_array($position) && is_int($position['offset'] ?? null)) {
            return $position['offset'];
        }

        return $default;
    }

    /**
     * @param array<mixed> $annotation
     */
    private function annotationName(array $annotation): ?string
    {
        $span = is_array($annotation['span'] ?? null) ? $annotation['span'] : [];
        $fileId = is_array($span['file_id'] ?? null) ? $span['file_id'] : [];
        $name = $fileId['name'] ?? null;

        return is_string($name) ? $name : null;
    }

    /**
     * Mago levels have no LSP columns, so notes and help (which carry no
     * location) are folded into the message rather than dropped.
     *
     * @param array<mixed> $issue
     */
    private function composeMessage(array $issue): string
    {
        $message = is_string($issue['message'] ?? null) ? $issue['message'] : '';

        foreach (is_array($issue['notes'] ?? null) ? $issue['notes'] : [] as $note) {
            if (is_string($note) && $note !== '') {
                $message .= "\n" . $note;
            }
        }

        if (is_string($issue['help'] ?? null) && $issue['help'] !== '') {
            $message .= "\n\n" . $issue['help'];
        }

        return $message;
    }

    /**
     * @return DiagnosticSeverity::*
     */
    private function severity(string $level): int
    {
        return match (strtolower($level)) {
            'warning' => DiagnosticSeverity::WARNING,
            'note' => DiagnosticSeverity::INFORMATION,
            'help' => DiagnosticSeverity::HINT,
            default => DiagnosticSeverity::ERROR,
        };
    }

    /**
     * @return array<mixed>
     */
    private function decodeJson(string $jsonString): array
    {
        $decoded = json_decode($jsonString, true);

        if (!is_array($decoded)) {
            throw new RuntimeException(sprintf(
                'Could not decode expected Mago JSON string "%s"',
                $jsonString
            ));
        }

        return $decoded;
    }
}
