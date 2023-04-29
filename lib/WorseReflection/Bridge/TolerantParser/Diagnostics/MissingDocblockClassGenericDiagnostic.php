<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Phpactor\Name\FullyQualifiedName;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;

class MissingDocblockClassGenericDiagnostic implements Diagnostic
{
    /**
     * @param array<string,Type> $missingArguments
     */
    public function __construct(
        private ByteOffsetRange $range,
        private ClassName $className,
        private ClassName $parentClassName,
        private array $missingParameters
    )
    {
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
        return sprintf(
            'Class "%s" extends generic class "%s" but does not provide a generic argument for parameters "%s"',
            $this->className->head(),
            $this->parentClassName->head(),
            implode('", "', array_keys($this->missingParameters))
        );
    }
}
