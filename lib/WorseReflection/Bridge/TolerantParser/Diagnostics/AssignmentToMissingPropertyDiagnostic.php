<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;
use Phpactor\WorseReflection\Core\Type;

class AssignmentToMissingPropertyDiagnostic implements Diagnostic
{
    public function __construct(
        private readonly ByteOffsetRange $range,
        private readonly string $classType,
        private readonly string $propertyName,
        private readonly Type $propertyType,
        private readonly bool $isSubscriptAssignment
    ) {
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

    public function tags(): array
    {
        return [];
    }

    public function code(): string
    {
        return 'assignment_to_missing_property';
    }
}
