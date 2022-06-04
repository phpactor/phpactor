<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;
use Phpactor\WorseReflection\Core\Type;

class AssignmentToMissingPropertyDiagnostic implements Diagnostic
{
    private ByteOffsetRange $range;

    private string $classType;

    private string $propertyName;

    private Type $propertyType;

    private bool $isSubscriptAssignment;

    public function __construct(
        ByteOffsetRange $range,
        string $classType,
        string $propertyName,
        Type $propertyType,
        bool $isSubscriptAssignment
    ) {
        $this->range = $range;
        $this->classType = $classType;
        $this->propertyName = $propertyName;
        $this->propertyType = $propertyType;
        $this->isSubscriptAssignment = $isSubscriptAssignment;
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
        return sprintf('Property "%s" has not been defined', $this->propertyName);
    }

    public function classType(): string
    {
        return $this->classType;
    }

    public function propertyName(): string
    {
        return $this->propertyName;
    }

    public function propertyType(): Type
    {
        return $this->propertyType;
    }

    public function isSubscriptAssignment(): bool
    {
        return $this->isSubscriptAssignment;
    }
}
