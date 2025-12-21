<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;
use Phpactor\WorseReflection\Core\DiagnosticTag;

class UnusedImportDiagnostic implements Diagnostic
{
    private function __construct(
        private ByteOffsetRange $range,
        private string $name
    ) {
    }

    public static function for(ByteOffsetRange $range, string $name): self
    {
        return new self($range, $name);
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
        return sprintf('Name "%s" is imported but not used', $this->name);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function tags(): array
    {
        return [DiagnosticTag::UNNECESSARY];
    }

    public function code(): string
    {
        return 'unused_import';
    }
}
