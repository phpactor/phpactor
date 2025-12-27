<?php

namespace Phpactor\WorseReflection\Core\DiagnosticProvider;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;

class BareDiagnostic implements Diagnostic
{
    public function __construct(
        private readonly ByteOffsetRange $range,
        private readonly DiagnosticSeverity $severity,
        private readonly string $message,
        private readonly string $code,
    ) {
    }

    public function range(): ByteOffsetRange
    {
        return $this->range;
    }

    public function severity(): DiagnosticSeverity
    {
        return $this->severity;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function tags(): array
    {
        return [];
    }

    public function code(): string
    {
        return $this->code;
    }
}
