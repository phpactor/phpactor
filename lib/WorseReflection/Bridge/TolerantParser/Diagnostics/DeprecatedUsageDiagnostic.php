<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;
use Phpactor\WorseReflection\Core\DiagnosticTag;

class DeprecatedUsageDiagnostic implements Diagnostic
{
    public function __construct(private ByteOffsetRange $range, private string $memberName, private string $message, private string $memberType)
    {
    }

    public function range(): ByteOffsetRange
    {
        return $this->range;
    }

    public function severity(): DiagnosticSeverity
    {
        return DiagnosticSeverity::WARNING();
    }

    public function message(): string
    {
        if (!$this->message) {
            return sprintf('Call to deprecated %s "%s"', $this->memberType, $this->memberName);
        }

        return sprintf('Call to deprecated %s "%s": %s', $this->memberType, $this->memberName, $this->message);
    }

    public function tags(): array
    {
        return [DiagnosticTag::DEPRECATED];
    }

    public function code(): string
    {
        return 'deprecated_usage';
    }
}
