<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;

class WrongVarOrderDiagnostic implements Diagnostic
{
    private string $message;

    private DiagnosticSeverity $severity;

    private ByteOffsetRange $range;

    public function __construct(
        ByteOffsetRange $range,
        string $message,
        ?DiagnosticSeverity $severity = null
    ) {
        $this->range = $range;
        $this->message = $message;
        $this->severity = $severity ?: DiagnosticSeverity::INFORMATION();
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
}
