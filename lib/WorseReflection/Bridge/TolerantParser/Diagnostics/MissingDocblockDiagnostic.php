<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;

class MissingDocblockDiagnostic implements Diagnostic
{
    private string $message;

    private DiagnosticSeverity $severity;

    private ByteOffsetRange $range;

    private string $classType;

    private string $methodName;

    private string $actualReturnType;

    public function __construct(
        ByteOffsetRange $range,
        string $message,
        DiagnosticSeverity $severity,
        string $classType,
        string $methodName,
        string $actualReturnType
    ) {
        $this->message = $message;
        $this->severity = $severity;
        $this->range = $range;
        $this->classType = $classType;
        $this->methodName = $methodName;
        $this->actualReturnType = $actualReturnType;
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
}
