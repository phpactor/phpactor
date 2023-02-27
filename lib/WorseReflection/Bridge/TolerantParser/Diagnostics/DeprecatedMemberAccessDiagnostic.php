<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;

class DeprecatedMemberAccessDiagnostic implements Diagnostic
{
    public function __construct(private ByteOffsetRange $range, private string $memberName, private string $message)
    {
    }

    public function range(): ByteOffsetRange
    {
        return $this->range;
    }

    public function severity(): DiagnosticSeverity
    {
        return DiagnosticSeverity::HINT();
    }

    public function message(): string
    {
        return sprintf('Call to deprecated member "%s": %s', $this->memberName, $this->message);
    }
}
