<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;
use Phpactor\WorseReflection\Core\Type\GenericClassType;

class DocblockMissingClassGenericDiagnostic implements Diagnostic
{

    public function __construct(
        private ByteOffsetRange $range,
        private ClassName $className,
        private GenericClassType $missingGenericType,
        private string $tagName,
    ) {
    }

    public function missingGenericType(): GenericClassType
    {
        return $this->missingGenericType;
    }

    public function range(): ByteOffsetRange
    {
        return $this->range;
    }

    public function severity(): DiagnosticSeverity
    {
        return DiagnosticSeverity::WARNING();
    }

    public function className(): ClassName
    {
        return $this->className;
    }

    public function message(): string
    {
        return sprintf(
            'Missing generic tag `%s %s`',
            $this->tagName,
            $this->missingGenericType->short()
        );
    }

    public function isExtends(): bool
    {
        return $this->tagName === '@extends';
    }

    public function tags(): array
    {
        return [];
    }
}
