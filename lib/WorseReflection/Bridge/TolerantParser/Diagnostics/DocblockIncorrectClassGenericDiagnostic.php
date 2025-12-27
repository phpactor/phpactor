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
        private readonly ByteOffsetRange $range,
        private readonly Type $givenType,
        private readonly GenericClassType $correctType,
        private readonly string $tagName,
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

    public function tags(): array
    {
        return [];
    }

    public function code(): string
    {
        return 'docblock_incorrect_class_generic';
    }
}
