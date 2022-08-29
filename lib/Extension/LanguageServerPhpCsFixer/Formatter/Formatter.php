<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer\Formatter;

use Phpactor\TextDocument\TextEdits;
use Phpactor\TextDocument\TextDocument;

interface Formatter
{
    public function format(TextDocument $document): TextEdits;
}
