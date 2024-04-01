<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;

class DocblockMissingReturnTypeDiagnostic implements Diagnostic
{
    public function __construct(
        private ByteOffsetRange $range,
        private string $message,
        private DiagnosticSeverity $severity,
        private string $classType,
        private string $methodName,
        private string $actualReturnType
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

    public function actualReturnType(): string
    {
        return $this->actualReturnType;
    }

    public function tags(): array
    {
        return [];
    }
}
