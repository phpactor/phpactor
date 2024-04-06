<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\TextDocument\ByteOffsetRange;

interface Diagnostic
{
    public function range(): ByteOffsetRange;

    public function severity(): DiagnosticSeverity;

    public function message(): string;

    /**
     * @return array<DiagnosticTag>
     */
    public function tags(): array;
}
