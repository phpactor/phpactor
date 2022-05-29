<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Diagnostic;

class MissingMethodDiagnostic implements Diagnostic
{
    private string $message;

    private int $severity;

    private ByteOffsetRange $range;

    private string $classType;

    private string $methodName;

    public function __construct(
        ByteOffsetRange $range,
        string $message,
        int $severity,
        string $classType,
        string $methodName,
    ) {
        $this->message = $message;
        $this->severity = $severity;
        $this->range = $range;
        $this->classType = $classType;
        $this->methodName = $methodName;
    }

    public function range(): ByteOffsetRange
    {
        return $this->range;
    }

    public function severity(): int
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
}
