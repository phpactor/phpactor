<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;

class MissingMemberDiagnostic implements Diagnostic
{
    public function __construct(
        private readonly ByteOffsetRange $range,
        private readonly string $message,
        private readonly DiagnosticSeverity $severity,
        private readonly string $classType,
        private readonly string $methodName,
        private readonly string $memberType,
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

    public function classType(): string
    {
        return $this->classType;
    }

    public function methodName(): string
    {
        return $this->methodName;
    }

    public function memberType(): string
    {
        return $this->memberType;
    }

    public function tags(): array
    {
        return [];
    }

    public function code(): string
    {
        return 'missing_member';
    }
}
