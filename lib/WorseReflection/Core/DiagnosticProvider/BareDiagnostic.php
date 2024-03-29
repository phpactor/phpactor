<?php

namespace Phpactor\WorseReflection\Core\DiagnosticProvider;

use Phpactor\LanguageServerProtocol\DiagnosticRelatedInformation;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;

class BareDiagnostic implements Diagnostic
{
    public function __construct(
        private ByteOffsetRange $range,
        private DiagnosticSeverity $severity,
        private string $message
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

    public function relatedInformation(): ?DiagnosticRelatedInformation
    {
        return null;
    }
}
