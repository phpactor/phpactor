<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;
use Phpactor\WorseReflection\Core\Type;

class DocblockMissingParamDiagnostic implements Diagnostic
{
    public function __construct(
        private ByteOffsetRange $range,
        private string $message,
        private DiagnosticSeverity $severity,
        private string $classType,
        private string $methodName,
        private string $paramName,
        private Type $paramType,
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

    public function paramName(): string
    {
        return $this->paramName;
    }

    public function paramType(): Type
    {
        return $this->paramType;
    }

    public function tags(): array
    {
        return [];
    }
}
