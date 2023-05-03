<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\GenericClassType;

class DocblockIncorrectClassGenericDiagnostic implements Diagnostic
{
    public function __construct(
        private ByteOffsetRange $range,
        private Type $givenType,
        private GenericClassType $correctType,
        private string $tagName,
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
        return sprintf(
            'Generic tag `%s %s` should be compatible with `%s %s`',
            $this->tagName,
            $this->givenType->short(),
            $this->tagName,
            $this->correctType->short()
        );
    }
}
