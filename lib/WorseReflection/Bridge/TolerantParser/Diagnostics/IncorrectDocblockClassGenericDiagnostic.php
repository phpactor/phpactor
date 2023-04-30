<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\GenericClassType;

class IncorrectDocblockClassGenericDiagnostic implements Diagnostic
{
    public function __construct(
        private ByteOffsetRange $range,
        private Type $givenType,
        private GenericClassType $correctType,
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
            'Generic tag `@extends %s` should be compatible with `@extends %s`',
            $this->givenType->__toString(),
            $this->correctType->__toString()
        );
    }
}
