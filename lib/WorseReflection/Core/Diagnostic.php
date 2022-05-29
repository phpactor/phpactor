<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\LanguageServerProtocol\DiagnosticSeverity;
use Phpactor\TextDocument\ByteOffsetRange;

interface Diagnostic
{
    public function range(): ByteOffsetRange;

    /**
     * @return DiagnosticSeverity::*
     */
    public function severity(): int;

    public function message(): string;
}
