<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\LanguageServerProtocol\DiagnosticRelatedInformation;
use Phpactor\TextDocument\ByteOffsetRange;

interface Diagnostic
{
    public function range(): ByteOffsetRange;

    public function severity(): DiagnosticSeverity;

    public function message(): string;

    public function relatedInformation(): ?DiagnosticRelatedInformation;
}
