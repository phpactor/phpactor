<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Phpactor\Name\FullyQualifiedName;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Psalm\Type;

class MissingDocblockClassGenericDiagnostic implements Diagnostic
{
    /**
     * @param array<string,Type> $missingArguments
     */
    public function __construct(
        private ByteOffsetRange $range,
        private ClassName $className,
        private ClassName $parentClassName,
        private GenericClassType $missingGenericType,
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
            'Class "%s" should provide docblock: @extends %s',
            $this->className->short(),
            $this->missingGenericType->__toString()
        );
    }
}
